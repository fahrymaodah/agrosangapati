<?php

namespace App\Http\Controllers;

use App\Services\HarvestDashboardService;
use Illuminate\Http\Request;
use Exception;

class HarvestDashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(HarvestDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get poktan harvest dashboard data.
     */
    public function poktanDashboard(Request $request, int $poktanId)
    {
        try {
            $data = $this->dashboardService->getPoktanDashboard($poktanId);

            return response()->json([
                'success' => true,
                'message' => 'Poktan harvest dashboard retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve poktan harvest dashboard',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get gapoktan harvest dashboard data.
     */
    public function gapoktanDashboard(Request $request, int $gapoktanId)
    {
        try {
            $data = $this->dashboardService->getGapoktanDashboard($gapoktanId);

            return response()->json([
                'success' => true,
                'message' => 'Gapoktan harvest dashboard retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve gapoktan harvest dashboard',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get poktan dashboard cards only (quick summary).
     */
    public function poktanDashboardCards(Request $request, int $poktanId)
    {
        try {
            $data = $this->dashboardService->getPoktanDashboardCards($poktanId);

            return response()->json([
                'success' => true,
                'message' => 'Poktan dashboard cards retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve poktan dashboard cards',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get gapoktan dashboard cards only (quick summary).
     */
    public function gapoktanDashboardCards(Request $request, int $gapoktanId)
    {
        try {
            $data = $this->dashboardService->getGapoktanDashboardCards($gapoktanId);

            return response()->json([
                'success' => true,
                'message' => 'Gapoktan dashboard cards retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve gapoktan dashboard cards',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
