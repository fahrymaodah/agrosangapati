<?php

namespace App\Http\Controllers;

use App\Services\CommodityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommodityController extends Controller
{
    protected CommodityService $commodityService;

    /**
     * CommodityController constructor.
     *
     * @param CommodityService $commodityService
     */
    public function __construct(CommodityService $commodityService)
    {
        $this->commodityService = $commodityService;
    }

    // ==================== COMMODITY ENDPOINTS ====================

    /**
     * Display a listing of commodities.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            if ($request->has('with_grades') && $request->with_grades) {
                $commodities = $this->commodityService->getAllCommoditiesWithGrades();
            } else {
                $commodities = $this->commodityService->getAllCommodities();
            }

            return response()->json([
                'success' => true,
                'message' => 'Commodities retrieved successfully',
                'data' => $commodities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve commodities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created commodity.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'unit' => 'required|string|max:50',
                'current_market_price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
            ]);

            $commodity = $this->commodityService->createCommodity($validated);

            return response()->json([
                'success' => true,
                'message' => 'Commodity created successfully',
                'data' => $commodity
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create commodity',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified commodity.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $commodity = $this->commodityService->getCommodityById($id);

            if (!$commodity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Commodity not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Commodity retrieved successfully',
                'data' => $commodity
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve commodity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified commodity.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'unit' => 'sometimes|string|max:50',
                'current_market_price' => 'sometimes|numeric|min:0',
                'description' => 'nullable|string',
            ]);

            $updated = $this->commodityService->updateCommodity($id, $validated);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Commodity not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Commodity updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update commodity',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified commodity.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->commodityService->deleteCommodity($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Commodity not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Commodity deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete commodity',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Search commodities by name.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'q' => 'required|string|min:1',
            ]);

            $commodities = $this->commodityService->searchCommodities($request->q);

            return response()->json([
                'success' => true,
                'message' => 'Search completed successfully',
                'data' => $commodities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    // ==================== GRADE ENDPOINTS ====================

    /**
     * Get all grades for a commodity.
     *
     * @param int $commodityId
     * @return JsonResponse
     */
    public function getGrades(int $commodityId): JsonResponse
    {
        try {
            $grades = $this->commodityService->getGradesByCommodity($commodityId);

            return response()->json([
                'success' => true,
                'message' => 'Grades retrieved successfully',
                'data' => $grades
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve grades',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new grade for a commodity.
     *
     * @param Request $request
     * @param int $commodityId
     * @return JsonResponse
     */
    public function storeGrade(Request $request, int $commodityId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'grade_name' => 'required|string|max:100',
                'price_modifier' => 'required|numeric', // percentage
                'description' => 'nullable|string',
            ]);

            $validated['commodity_id'] = $commodityId;

            $grade = $this->commodityService->createGrade($validated);

            return response()->json([
                'success' => true,
                'message' => 'Grade created successfully',
                'data' => $grade
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create grade',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified grade.
     *
     * @param int $commodityId
     * @param int $gradeId
     * @return JsonResponse
     */
    public function showGrade(int $commodityId, int $gradeId): JsonResponse
    {
        try {
            $grade = $this->commodityService->getGradeById($gradeId);

            if (!$grade || $grade->commodity_id != $commodityId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grade not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Grade retrieved successfully',
                'data' => $grade
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve grade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified grade.
     *
     * @param Request $request
     * @param int $commodityId
     * @param int $gradeId
     * @return JsonResponse
     */
    public function updateGrade(Request $request, int $commodityId, int $gradeId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'grade_name' => 'sometimes|string|max:100',
                'price_modifier' => 'sometimes|numeric',
                'description' => 'nullable|string',
            ]);

            $validated['commodity_id'] = $commodityId;

            $updated = $this->commodityService->updateGrade($gradeId, $validated);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grade not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Grade updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update grade',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified grade.
     *
     * @param int $commodityId
     * @param int $gradeId
     * @return JsonResponse
     */
    public function destroyGrade(int $commodityId, int $gradeId): JsonResponse
    {
        try {
            $deleted = $this->commodityService->deleteGrade($gradeId);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grade not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Grade deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete grade',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get all grades (across all commodities).
     *
     * @return JsonResponse
     */
    public function getAllGrades(): JsonResponse
    {
        try {
            $grades = $this->commodityService->getAllGrades();

            return response()->json([
                'success' => true,
                'message' => 'All grades retrieved successfully',
                'data' => $grades
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve grades',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
