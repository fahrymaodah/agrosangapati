<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MarketingDashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MarketingDashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(MarketingDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get complete marketing dashboard.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
        ];

        $dashboard = $this->dashboardService->getDashboard($filters);

        return response()->json([
            'success' => true,
            'message' => 'Marketing dashboard retrieved successfully.',
            'data' => $dashboard,
        ]);
    }

    /**
     * Get summary cards only.
     */
    public function summary(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());

        $summary = $this->dashboardService->getSummaryCards($startDate, $endDate);

        return response()->json([
            'success' => true,
            'message' => 'Summary cards retrieved successfully.',
            'data' => $summary,
        ]);
    }

    /**
     * Get revenue trend for chart.
     */
    public function revenueTrend(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());
        $groupBy = $request->query('group_by', 'day');

        $trend = $this->dashboardService->getRevenueTrend($startDate, $endDate, $groupBy);

        return response()->json([
            'success' => true,
            'message' => 'Revenue trend retrieved successfully.',
            'data' => $trend,
        ]);
    }

    /**
     * Get top selling products.
     */
    public function topProducts(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());
        $limit = $request->query('limit', 5);

        $products = $this->dashboardService->getTopProducts($startDate, $endDate, $limit);

        return response()->json([
            'success' => true,
            'message' => 'Top products retrieved successfully.',
            'data' => $products,
        ]);
    }

    /**
     * Get recent orders.
     */
    public function recentOrders(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 10);

        $orders = $this->dashboardService->getRecentOrders($limit);

        return response()->json([
            'success' => true,
            'message' => 'Recent orders retrieved successfully.',
            'data' => $orders,
        ]);
    }

    /**
     * Get pending payments to poktan.
     */
    public function pendingPayments(): JsonResponse
    {
        $payments = $this->dashboardService->getPendingPayments();

        return response()->json([
            'success' => true,
            'message' => 'Pending payments retrieved successfully.',
            'data' => $payments,
        ]);
    }

    /**
     * Get quick summary (current month).
     */
    public function quickSummary(): JsonResponse
    {
        $summary = $this->dashboardService->getQuickSummary();

        return response()->json([
            'success' => true,
            'message' => 'Quick summary retrieved successfully.',
            'data' => $summary,
        ]);
    }
}
