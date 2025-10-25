<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SalesReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SalesReportController extends Controller
{
    protected $reportService;

    public function __construct(SalesReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Get sales report by product.
     */
    public function byProduct(Request $request): JsonResponse
    {
        $filters = [
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'product_id' => $request->query('product_id'),
        ];

        $report = $this->reportService->getSalesByProduct($filters);

        return response()->json([
            'success' => true,
            'message' => 'Sales report by product retrieved successfully.',
            'data' => $report,
        ]);
    }

    /**
     * Get sales report by poktan.
     */
    public function byPoktan(Request $request): JsonResponse
    {
        $filters = [
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'poktan_id' => $request->query('poktan_id'),
        ];

        $report = $this->reportService->getSalesByPoktan($filters);

        return response()->json([
            'success' => true,
            'message' => 'Sales report by poktan retrieved successfully.',
            'data' => $report,
        ]);
    }

    /**
     * Get best selling products.
     */
    public function bestSelling(Request $request): JsonResponse
    {
        $filters = [
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'limit' => $request->query('limit', 10),
        ];

        $report = $this->reportService->getBestSellingProducts($filters);

        return response()->json([
            'success' => true,
            'message' => 'Best selling products retrieved successfully.',
            'data' => $report,
        ]);
    }

    /**
     * Get revenue analysis with trends.
     */
    public function revenueAnalysis(Request $request): JsonResponse
    {
        $filters = [
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'group_by' => $request->query('group_by', 'day'), // day, week, month, year
        ];

        $report = $this->reportService->getRevenueAnalysis($filters);

        return response()->json([
            'success' => true,
            'message' => 'Revenue analysis retrieved successfully.',
            'data' => $report,
        ]);
    }

    /**
     * Get sales summary statistics.
     */
    public function summary(Request $request): JsonResponse
    {
        $filters = [
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
        ];

        $summary = $this->reportService->getSalesSummary($filters);

        return response()->json([
            'success' => true,
            'message' => 'Sales summary retrieved successfully.',
            'data' => $summary,
        ]);
    }

    /**
     * Get top customers.
     */
    public function topCustomers(Request $request): JsonResponse
    {
        $filters = [
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'limit' => $request->query('limit', 10),
        ];

        $report = $this->reportService->getTopCustomers($filters);

        return response()->json([
            'success' => true,
            'message' => 'Top customers retrieved successfully.',
            'data' => $report,
        ]);
    }

    /**
     * Get complete sales report (all data combined).
     */
    public function complete(Request $request): JsonResponse
    {
        $filters = [
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'group_by' => $request->query('group_by', 'day'),
            'limit' => $request->query('limit', 10),
        ];

        $report = $this->reportService->getCompleteSalesReport($filters);

        return response()->json([
            'success' => true,
            'message' => 'Complete sales report retrieved successfully.',
            'data' => $report,
        ]);
    }
}
