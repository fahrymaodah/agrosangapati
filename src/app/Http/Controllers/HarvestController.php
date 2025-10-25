<?php

namespace App\Http\Controllers;

use App\Services\HarvestService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class HarvestController extends Controller
{
    protected HarvestService $harvestService;

    public function __construct(HarvestService $harvestService)
    {
        $this->harvestService = $harvestService;
    }

    /**
     * Get all harvests for specified poktan.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get poktan_id from query parameter
            $poktanId = $request->query('poktan_id');

            if (!$poktanId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Poktan ID is required',
                ], 400);
            }

            $harvests = $this->harvestService->getAllByPoktan($poktanId);

            return response()->json([
                'success' => true,
                'message' => 'Harvests retrieved successfully',
                'data' => $harvests,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve harvests',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get harvests by member.
     */
    public function byMember(Request $request, int $memberId): JsonResponse
    {
        try {
            $harvests = $this->harvestService->getByMember($memberId);

            return response()->json([
                'success' => true,
                'message' => 'Member harvests retrieved successfully',
                'data' => $harvests,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve member harvests',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get harvests by date range.
     */
    public function byDateRange(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'poktan_id' => 'nullable|integer|exists:poktans,id',
            ]);

            $harvests = $this->harvestService->getByDateRange(
                $validated['start_date'],
                $validated['end_date'],
                $validated['poktan_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Harvests retrieved successfully',
                'data' => $harvests,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve harvests',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get harvests by status.
     */
    public function byStatus(Request $request, string $status): JsonResponse
    {
        try {
            $poktanId = $request->query('poktan_id');
            
            if (!$poktanId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Poktan ID is required',
                ], 400);
            }

            $harvests = $this->harvestService->getByStatus($status, $poktanId);

            return response()->json([
                'success' => true,
                'message' => "Harvests with status '{$status}' retrieved successfully",
                'data' => $harvests,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve harvests',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get harvest summary for poktan.
     */
    public function summary(Request $request): JsonResponse
    {
        try {
            $poktanId = $request->query('poktan_id');

            if (!$poktanId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Poktan ID is required',
                ], 400);
            }

            $summary = $this->harvestService->getSummary($poktanId);

            return response()->json([
                'success' => true,
                'message' => 'Harvest summary retrieved successfully',
                'data' => $summary,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve harvest summary',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show single harvest.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $harvest = $this->harvestService->getById($id);

            if (!$harvest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Harvest not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Harvest retrieved successfully',
                'data' => $harvest,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve harvest',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create new harvest.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'member_id' => 'required|integer|exists:users,id',
                'poktan_id' => 'required|integer|exists:poktans,id',
                'commodity_id' => 'required|integer|exists:commodities,id',
                'grade_id' => 'required|integer|exists:commodity_grades,id',
                'quantity' => 'required|numeric|min:0.01',
                'unit' => 'nullable|string|max:20',
                'harvest_date' => 'required|date|before_or_equal:today',
                'harvest_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'status' => 'nullable|in:stored,sold,damaged',
                'notes' => 'nullable|string|max:1000',
            ]);

            $harvest = $this->harvestService->create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Harvest recorded successfully',
                'data' => $harvest,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record harvest',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update harvest.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'commodity_id' => 'nullable|integer|exists:commodities,id',
                'grade_id' => 'nullable|integer|exists:commodity_grades,id',
                'quantity' => 'nullable|numeric|min:0.01',
                'unit' => 'nullable|string|max:20',
                'harvest_date' => 'nullable|date|before_or_equal:today',
                'harvest_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'status' => 'nullable|in:stored,sold,damaged',
                'notes' => 'nullable|string|max:1000',
            ]);

            $harvest = $this->harvestService->update($id, $validated);

            if (!$harvest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Harvest not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Harvest updated successfully',
                'data' => $harvest,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update harvest',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update harvest status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:stored,sold,damaged',
            ]);

            $harvest = $this->harvestService->updateStatus($id, $validated['status']);

            if (!$harvest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Harvest not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Harvest status updated successfully',
                'data' => $harvest,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update harvest status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete harvest.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->harvestService->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Harvest not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Harvest deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete harvest',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
