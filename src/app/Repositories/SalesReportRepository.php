<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SalesDistribution;
use Illuminate\Support\Facades\DB;

class SalesReportRepository
{
    /**
     * Get sales report by product within date range.
     */
    public function getSalesByProduct(string $startDate, string $endDate, ?int $productId = null): array
    {
        $query = OrderItem::with(['product.commodity', 'product.grade', 'order'])
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(subtotal) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_id) as order_count')
            )
            ->whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->where('payment_status', 'paid')
                  ->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_revenue');

        if ($productId) {
            $query->where('product_id', $productId);
        }

        $results = $query->get();

        return $results->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name ?? 'Unknown',
                'commodity' => $item->product->commodity->name ?? 'Unknown',
                'grade' => $item->product->grade->name ?? 'Unknown',
                'total_quantity' => $item->total_quantity,
                'unit' => $item->product->unit ?? 'kg',
                'total_revenue' => $item->total_revenue,
                'order_count' => $item->order_count,
                'average_price' => $item->total_quantity > 0 
                    ? round($item->total_revenue / $item->total_quantity, 2) 
                    : 0,
            ];
        })->toArray();
    }

    /**
     * Get sales report by poktan within date range.
     */
    public function getSalesByPoktan(string $startDate, string $endDate, ?int $poktanId = null): array
    {
        $query = SalesDistribution::with(['poktan', 'commodity'])
            ->select(
                'poktan_id',
                'commodity_id',
                DB::raw('SUM(quantity_sold) as total_quantity'),
                DB::raw('SUM(total_revenue) as total_revenue'),
                DB::raw('SUM(gapoktan_margin) as total_margin'),
                DB::raw('SUM(poktan_payment) as total_payment'),
                DB::raw('COUNT(*) as distribution_count')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('poktan_id', 'commodity_id')
            ->orderByDesc('total_revenue');

        if ($poktanId) {
            $query->where('poktan_id', $poktanId);
        }

        $results = $query->get();

        return $results->map(function ($item) {
            return [
                'poktan_id' => $item->poktan_id,
                'poktan_name' => $item->poktan->name ?? 'Unknown',
                'commodity_id' => $item->commodity_id,
                'commodity_name' => $item->commodity->name ?? 'Unknown',
                'total_quantity' => $item->total_quantity,
                'total_revenue' => $item->total_revenue,
                'total_margin' => $item->total_margin,
                'total_payment' => $item->total_payment,
                'distribution_count' => $item->distribution_count,
                'paid_count' => SalesDistribution::where('poktan_id', $item->poktan_id)
                    ->where('commodity_id', $item->commodity_id)
                    ->where('payment_status', 'paid')
                    ->whereBetween('created_at', [$item->created_at, now()])
                    ->count(),
            ];
        })->toArray();
    }

    /**
     * Get best selling products.
     */
    public function getBestSellingProducts(string $startDate, string $endDate, int $limit = 10): array
    {
        $results = OrderItem::with(['product.commodity', 'product.grade'])
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(subtotal) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_id) as order_count')
            )
            ->whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->where('payment_status', 'paid')
                  ->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();

        return $results->map(function ($item, $index) {
            return [
                'rank' => $index + 1,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name ?? 'Unknown',
                'commodity' => $item->product->commodity->name ?? 'Unknown',
                'grade' => $item->product->grade->name ?? 'Unknown',
                'total_quantity' => $item->total_quantity,
                'unit' => $item->product->unit ?? 'kg',
                'total_revenue' => $item->total_revenue,
                'order_count' => $item->order_count,
                'average_order_size' => $item->order_count > 0 
                    ? round($item->total_quantity / $item->order_count, 2) 
                    : 0,
            ];
        })->toArray();
    }

    /**
     * Get revenue analysis with trends.
     */
    public function getRevenueAnalysis(string $startDate, string $endDate, string $groupBy = 'day'): array
    {
        $dateFormat = match($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m-%d',
        };

        $results = Order::select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('SUM(shipping_cost) as total_shipping'),
                DB::raw('SUM(grand_total) as grand_total'),
                DB::raw('COUNT(CASE WHEN payment_status = "paid" THEN 1 END) as paid_orders'),
                DB::raw('COUNT(CASE WHEN payment_status = "unpaid" THEN 1 END) as unpaid_orders')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return $results->map(function ($item) {
            return [
                'period' => $item->period,
                'order_count' => $item->order_count,
                'total_revenue' => $item->total_revenue,
                'total_shipping' => $item->total_shipping,
                'grand_total' => $item->grand_total,
                'paid_orders' => $item->paid_orders,
                'unpaid_orders' => $item->unpaid_orders,
                'average_order_value' => $item->order_count > 0 
                    ? round($item->total_revenue / $item->order_count, 2) 
                    : 0,
            ];
        })->toArray();
    }

    /**
     * Get sales summary statistics.
     */
    public function getSalesSummary(string $startDate, string $endDate): array
    {
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])->get();
        
        $totalOrders = $orders->count();
        $totalRevenue = $orders->where('payment_status', 'paid')->sum('total_amount');
        $totalShipping = $orders->where('payment_status', 'paid')->sum('shipping_cost');
        $paidOrders = $orders->where('payment_status', 'paid')->count();
        $unpaidOrders = $orders->where('payment_status', 'unpaid')->count();
        
        $ordersByStatus = Order::select('order_status', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('order_status')
            ->get()
            ->pluck('count', 'order_status')
            ->toArray();

        // Get distribution data
        $distributions = SalesDistribution::whereBetween('created_at', [$startDate, $endDate])->get();
        $totalDistributions = $distributions->count();
        $totalMargin = $distributions->sum('gapoktan_margin');
        $totalPoktanPayment = $distributions->sum('poktan_payment');
        $paidDistributions = $distributions->where('payment_status', 'paid')->count();

        // Get product stats
        $totalProducts = Product::where('status', 'available')->count();
        $productsSold = OrderItem::whereHas('order', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        })->distinct('product_id')->count();

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'orders' => [
                'total_orders' => $totalOrders,
                'paid_orders' => $paidOrders,
                'unpaid_orders' => $unpaidOrders,
                'total_revenue' => $totalRevenue,
                'total_shipping' => $totalShipping,
                'grand_total' => $totalRevenue + $totalShipping,
                'average_order_value' => $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0,
                'by_status' => $ordersByStatus,
            ],
            'distributions' => [
                'total_distributions' => $totalDistributions,
                'paid_distributions' => $paidDistributions,
                'pending_distributions' => $totalDistributions - $paidDistributions,
                'total_margin' => $totalMargin,
                'total_poktan_payment' => $totalPoktanPayment,
            ],
            'products' => [
                'total_active_products' => $totalProducts,
                'products_sold' => $productsSold,
            ],
        ];
    }

    /**
     * Get top customers (buyers).
     */
    public function getTopCustomers(string $startDate, string $endDate, int $limit = 10): array
    {
        $results = Order::select(
                'buyer_name',
                'buyer_phone',
                'buyer_email',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_spent'),
                DB::raw('SUM(grand_total) as grand_total'),
                DB::raw('MAX(created_at) as last_order_date')
            )
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('buyer_name', 'buyer_phone', 'buyer_email')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get();

        return $results->map(function ($item, $index) {
            return [
                'rank' => $index + 1,
                'buyer_name' => $item->buyer_name,
                'buyer_phone' => $item->buyer_phone,
                'buyer_email' => $item->buyer_email,
                'order_count' => $item->order_count,
                'total_spent' => $item->total_spent,
                'grand_total' => $item->grand_total,
                'average_order_value' => $item->order_count > 0 
                    ? round($item->total_spent / $item->order_count, 2) 
                    : 0,
                'last_order_date' => $item->last_order_date,
            ];
        })->toArray();
    }
}
