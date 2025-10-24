<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get Poktan dashboard data
     * 
     * @group Dashboard
     * @urlParam poktan_id integer required The ID of the Poktan. Example: 1
     * @queryParam start_date string optional Start date for filtering (Y-m-d format). Example: 2025-10-01
     * @queryParam end_date string optional End date for filtering (Y-m-d format). Example: 2025-10-31
     */
    public function poktanDashboard(Request $request, int $poktanId): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $result = $this->dashboardService->getPoktanDashboard(
            $poktanId,
            $request->input('start_date'),
            $request->input('end_date')
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 404);
        }

        return response()->json($result);
    }

    /**
     * Get Gapoktan dashboard data (consolidated from all Poktans)
     * 
     * @group Dashboard
     * @urlParam gapoktan_id integer required The ID of the Gapoktan. Example: 1
     * @queryParam start_date string optional Start date for filtering (Y-m-d format). Example: 2025-10-01
     * @queryParam end_date string optional End date for filtering (Y-m-d format). Example: 2025-10-31
     */
    public function gapoktanDashboard(Request $request, int $gapoktanId): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $result = $this->dashboardService->getGapoktanDashboard(
            $gapoktanId,
            $request->input('start_date'),
            $request->input('end_date')
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 404);
        }

        return response()->json($result);
    }
}
