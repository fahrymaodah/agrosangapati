<?php

namespace App\Services;

use App\Repositories\SalesReportRepository;
use App\Models\Order;
use App\Models\Product;
use App\Models\SalesDistribution;
use Illuminate\Support\Facades\DB;

class MarketingDashboardService
{
    protected $salesReportRepository;

    public function __construct(SalesReportRepository $salesReportRepository)
    {
        $this->salesReportRepository = $salesReportRepository;
    }

    /**
     * Get complete marketing dashboard data.
     */
    public function getDashboard(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->endOfMonth()->toDateString();

        return [
            'summary' => $this->getSummaryCards($startDate, $endDate),
            'revenue_trend' => $this->getRevenueTrend($startDate, $endDate),
            'top_products' => $this->getTopProducts($startDate, $endDate, 5),
            'recent_orders' => $this->getRecentOrders(10),
            'pending_payments' => $this->getPendingPayments(),
            'order_status_breakdown' => $this->getOrderStatusBreakdown($startDate, $endDate),
            'payment_status_breakdown' => $this->getPaymentStatusBreakdown($startDate, $endDate),
        ];
    }

    /**
     * Get summary cards data.
     */
    public function getSummaryCards(string $startDate, string $endDate): array
    {
        // Total Revenue (paid orders)
        $totalRevenue = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        // Total Orders
        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();

        // Pending Orders (pending, confirmed, processing)
        $pendingOrders = Order::whereIn('order_status', ['pending', 'confirmed', 'processing'])
            ->count();

        // Active Products
        $activeProducts = Product::where('status', 'available')->count();

        // Total Products
        $totalProducts = Product::count();

        // Pending Payments to Poktan
        $pendingPayments = SalesDistribution::where('payment_status', 'pending')
            ->sum('poktan_payment');

        // Orders this month vs last month
        $lastMonthStart = now()->subMonth()->startOfMonth()->toDateString();
        $lastMonthEnd = now()->subMonth()->endOfMonth()->toDateString();
        $lastMonthOrders = Order::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $orderGrowth = $lastMonthOrders > 0 
            ? round((($totalOrders - $lastMonthOrders) / $lastMonthOrders) * 100, 1)
            : 0;

        // Revenue this month vs last month
        $lastMonthRevenue = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('total_amount');
        $revenueGrowth = $lastMonthRevenue > 0 
            ? round((($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        return [
            'total_revenue' => [
                'value' => $totalRevenue,
                'formatted' => 'Rp ' . number_format($totalRevenue, 0, ',', '.'),
                'growth' => $revenueGrowth,
                'trend' => $revenueGrowth >= 0 ? 'up' : 'down',
            ],
            'total_orders' => [
                'value' => $totalOrders,
                'growth' => $orderGrowth,
                'trend' => $orderGrowth >= 0 ? 'up' : 'down',
            ],
            'pending_orders' => [
                'value' => $pendingOrders,
            ],
            'active_products' => [
                'value' => $activeProducts,
                'total' => $totalProducts,
                'percentage' => $totalProducts > 0 ? round(($activeProducts / $totalProducts) * 100, 1) : 0,
            ],
            'pending_payments' => [
                'value' => $pendingPayments,
                'formatted' => 'Rp ' . number_format($pendingPayments, 0, ',', '.'),
                'count' => SalesDistribution::where('payment_status', 'pending')->count(),
            ],
        ];
    }

    /**
     * Get revenue trend for chart.
     */
    public function getRevenueTrend(string $startDate, string $endDate, string $groupBy = 'day'): array
    {
        $dateFormat = match($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $results = Order::select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(CASE WHEN payment_status = "paid" THEN total_amount ELSE 0 END) as revenue'),
                DB::raw('SUM(grand_total) as grand_total')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return $results->map(function ($item) {
            return [
                'period' => $item->period,
                'order_count' => $item->order_count,
                'revenue' => $item->revenue,
                'revenue_formatted' => 'Rp ' . number_format($item->revenue, 0, ',', '.'),
                'grand_total' => $item->grand_total,
            ];
        })->toArray();
    }

    /**
     * Get top selling products.
     */
    public function getTopProducts(string $startDate, string $endDate, int $limit = 5): array
    {
        return $this->salesReportRepository->getBestSellingProducts($startDate, $endDate, $limit);
    }

    /**
     * Get recent orders.
     */
    public function getRecentOrders(int $limit = 10): array
    {
        $orders = Order::with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'buyer_name' => $order->buyer_name,
                'buyer_phone' => $order->buyer_phone,
                'total_amount' => $order->total_amount,
                'total_amount_formatted' => 'Rp ' . number_format($order->total_amount, 0, ',', '.'),
                'grand_total' => $order->grand_total,
                'order_status' => $order->order_status,
                'payment_status' => $order->payment_status,
                'items_count' => $order->items->count(),
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'created_at_human' => $order->created_at->diffForHumans(),
            ];
        })->toArray();
    }

    /**
     * Get pending payments to poktan.
     */
    public function getPendingPayments(): array
    {
        $distributions = SalesDistribution::with(['poktan', 'orderItem.product'])
            ->where('payment_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = $distributions->groupBy('poktan_id')->map(function ($items, $poktanId) {
            $firstItem = $items->first();
            return [
                'poktan_id' => $poktanId,
                'poktan_name' => $firstItem->poktan->name ?? 'Unknown',
                'total_amount' => $items->sum('poktan_payment'),
                'total_amount_formatted' => 'Rp ' . number_format($items->sum('poktan_payment'), 0, ',', '.'),
                'distribution_count' => $items->count(),
                'oldest_date' => $items->min('created_at'),
            ];
        })->values()->toArray();

        return [
            'summary' => $summary,
            'total_pending' => $distributions->sum('poktan_payment'),
            'total_pending_formatted' => 'Rp ' . number_format($distributions->sum('poktan_payment'), 0, ',', '.'),
            'total_count' => $distributions->count(),
        ];
    }

    /**
     * Get order status breakdown.
     */
    public function getOrderStatusBreakdown(string $startDate, string $endDate): array
    {
        $results = Order::select('order_status', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('order_status')
            ->get();

        $total = $results->sum('count');

        return $results->map(function ($item) use ($total) {
            return [
                'status' => $item->order_status,
                'count' => $item->count,
                'percentage' => $total > 0 ? round(($item->count / $total) * 100, 1) : 0,
            ];
        })->toArray();
    }

    /**
     * Get payment status breakdown.
     */
    public function getPaymentStatusBreakdown(string $startDate, string $endDate): array
    {
        $results = Order::select('payment_status', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('payment_status')
            ->get();

        $total = $results->sum('count');

        return $results->map(function ($item) use ($total) {
            return [
                'status' => $item->payment_status,
                'count' => $item->count,
                'percentage' => $total > 0 ? round(($item->count / $total) * 100, 1) : 0,
            ];
        })->toArray();
    }

    /**
     * Get summary cards only (quick endpoint).
     */
    public function getQuickSummary(): array
    {
        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->endOfMonth()->toDateString();
        
        return $this->getSummaryCards($startDate, $endDate);
    }
}
