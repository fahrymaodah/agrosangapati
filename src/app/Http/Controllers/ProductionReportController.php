<?php

namespace App\Http\Controllers;

use App\Services\ProductionReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class ProductionReportController extends Controller
{
    protected ProductionReportService $service;

    public function __construct(ProductionReportService $service)
    {
        $this->service = $service;
    }

    /**
     * Get member's complete production report.
     */
    public function memberReport(Request $request, int $memberId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $report = $this->service->getMemberReport(
                $memberId,
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Member production report retrieved successfully',
                'data' => $report,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve member production report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get member's production summary.
     */
    public function memberSummary(Request $request, int $memberId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $summary = $this->service->getMemberSummary(
                $memberId,
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Member production summary retrieved successfully',
                'data' => $summary,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve member production summary',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get member's production by commodity.
     */
    public function memberByCommodity(Request $request, int $memberId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $data = $this->service->getMemberByCommodity(
                $memberId,
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Member production by commodity retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve member production by commodity',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get member's production comparison.
     */
    public function memberComparison(Request $request, int $memberId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'period' => 'nullable|in:week,month,quarter,year',
            ]);

            $comparison = $this->service->getMemberComparison(
                $memberId,
                $validated['period'] ?? 'month'
            );

            return response()->json([
                'success' => true,
                'message' => 'Member production comparison retrieved successfully',
                'data' => $comparison,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve member production comparison',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get top producers in poktan.
     */
    public function topProducers(Request $request, int $poktanId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'limit' => 'nullable|integer|min:1|max:50',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $data = $this->service->getTopProducers(
                $poktanId,
                $validated['limit'] ?? 10,
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Top producers retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve top producers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get poktan's complete production report.
     */
    public function poktanReport(Request $request, int $poktanId)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $data = $this->service->getPoktanReport(
                $poktanId,
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Poktan production report retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve poktan production report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get poktan's production summary.
     */
    public function poktanSummary(Request $request, int $poktanId)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $data = $this->service->getPoktanSummary(
                $poktanId,
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Poktan production summary retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve poktan production summary',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get poktan's production by commodity.
     */
    public function poktanByCommodity(Request $request, int $poktanId)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $data = $this->service->getPoktanByCommodity(
                $poktanId,
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Poktan production by commodity retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve poktan production by commodity',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get poktan's production by member.
     */
    public function poktanByMember(Request $request, int $poktanId)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $data = $this->service->getPoktanByMember(
                $poktanId,
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Poktan production by member retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve poktan production by member',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get poktan's monthly production trend.
     */
    public function poktanMonthlyTrend(Request $request, int $poktanId)
    {
        try {
            $validated = $request->validate([
                'months' => 'nullable|integer|min:1|max:24',
            ]);

            $data = $this->service->getPoktanMonthlyTrend(
                $poktanId,
                $validated['months'] ?? 12
            );

            return response()->json([
                'success' => true,
                'message' => 'Poktan monthly trend retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve poktan monthly trend',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get gapoktan's complete production report.
     */
    public function gapoktanReport(Request $request, int $gapoktanId)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $data = $this->service->getGapoktanReport(
                $gapoktanId,
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Gapoktan production report retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve gapoktan production report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get gapoktan's production summary.
     */
    public function gapoktanSummary(Request $request, int $gapoktanId)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $data = $this->service->getGapoktanSummary(
                $gapoktanId,
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Gapoktan production summary retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve gapoktan production summary',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get gapoktan's production by commodity.
     */
    public function gapoktanByCommodity(Request $request, int $gapoktanId)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $data = $this->service->getGapoktanByCommodity(
                $gapoktanId,
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Gapoktan production by commodity retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve gapoktan production by commodity',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get gapoktan's production by poktan.
     */
    public function gapoktanByPoktan(Request $request, int $gapoktanId)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $data = $this->service->getGapoktanByPoktan(
                $gapoktanId,
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Gapoktan production by poktan retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve gapoktan production by poktan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get gapoktan's poktan comparison/ranking.
     */
    public function gapoktanPoktanComparison(Request $request, int $gapoktanId)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $data = $this->service->getGapoktanPoktanComparison(
                $gapoktanId,
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Gapoktan poktan comparison retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve gapoktan poktan comparison',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get gapoktan's monthly production trend.
     */
    public function gapoktanMonthlyTrend(Request $request, int $gapoktanId)
    {
        try {
            $validated = $request->validate([
                'months' => 'nullable|integer|min:1|max:24',
            ]);

            $data = $this->service->getGapoktanMonthlyTrend(
                $gapoktanId,
                $validated['months'] ?? 12
            );

            return response()->json([
                'success' => true,
                'message' => 'Gapoktan monthly trend retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve gapoktan monthly trend',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
