<?php

namespace App\Http\Controllers;

use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class StockController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Get all stocks for poktan.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $poktanId = $request->query('poktan_id');

            if (!$poktanId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Poktan ID is required',
                ], 400);
            }

            $result = $this->stockService->getAllByPoktan($poktanId);

            return response()->json([
                'success' => true,
                'message' => 'Stocks retrieved successfully',
                'data' => $result['stocks'],
                'total_items' => $result['total_items'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve stocks',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get low stock items.
     */
    public function lowStock(Request $request): JsonResponse
    {
        try {
            $poktanId = $request->query('poktan_id');
            $minimumQuantity = $request->query('minimum', 100);

            if (!$poktanId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Poktan ID is required',
                ], 400);
            }

            $stocks = $this->stockService->getLowStock($poktanId, $minimumQuantity);

            return response()->json([
                'success' => true,
                'message' => 'Low stock items retrieved successfully',
                'data' => $stocks,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve low stock items',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get stocks by location.
     */
    public function byLocation(Request $request): JsonResponse
    {
        try {
            $poktanId = $request->query('poktan_id');
            $location = $request->query('location');

            if (!$poktanId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Poktan ID is required',
                ], 400);
            }

            $stocks = $this->stockService->getByLocation($poktanId, $location);

            return response()->json([
                'success' => true,
                'message' => 'Stocks by location retrieved successfully',
                'data' => $stocks,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve stocks by location',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get stock summary.
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

            $summary = $this->stockService->getSummary($poktanId);

            return response()->json([
                'success' => true,
                'message' => 'Stock summary retrieved successfully',
                'data' => $summary,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve stock summary',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show single stock.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $stock = $this->stockService->getById($id);

            if (!$stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock retrieved successfully',
                'data' => $stock,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve stock',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add stock (incoming).
     */
    public function addStock(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'poktan_id' => 'required|exists:poktans,id',
                'commodity_id' => 'required|exists:commodities,id',
                'grade_id' => 'required|exists:commodity_grades,id',
                'quantity' => 'required|numeric|min:0.01',
                'unit' => 'required|string|max:20',
                'location' => 'nullable|string|max:255',
                'reference_type' => 'nullable|string|max:50',
                'reference_id' => 'nullable|integer',
                'notes' => 'nullable|string',
            ]);

            // Temporary user ID (will be from auth later)
            $userId = 1;

            $stock = $this->stockService->addStock($validated, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Stock added successfully',
                'data' => $stock,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add stock',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove stock (outgoing).
     */
    public function removeStock(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'poktan_id' => 'required|exists:poktans,id',
                'commodity_id' => 'required|exists:commodities,id',
                'grade_id' => 'required|exists:commodity_grades,id',
                'quantity' => 'required|numeric|min:0.01',
                'location' => 'nullable|string|max:255',
                'reference_type' => 'nullable|string|max:50',
                'reference_id' => 'nullable|integer',
                'notes' => 'nullable|string',
            ]);

            // Temporary user ID
            $userId = 1;

            $stock = $this->stockService->removeStock($validated, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Stock removed successfully',
                'data' => $stock,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove stock',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Transfer stock between locations.
     */
    public function transferStock(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'poktan_id' => 'required|exists:poktans,id',
                'commodity_id' => 'required|exists:commodities,id',
                'grade_id' => 'required|exists:commodity_grades,id',
                'quantity' => 'required|numeric|min:0.01',
                'from_location' => 'required|string|max:255',
                'to_location' => 'required|string|max:255',
                'notes' => 'nullable|string',
            ]);

            // Temporary user ID
            $userId = 1;

            $result = $this->stockService->transferStock($validated, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Stock transferred successfully',
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to transfer stock',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Record damaged stock.
     */
    public function recordDamage(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'poktan_id' => 'required|exists:poktans,id',
                'commodity_id' => 'required|exists:commodities,id',
                'grade_id' => 'required|exists:commodity_grades,id',
                'quantity' => 'required|numeric|min:0.01',
                'location' => 'nullable|string|max:255',
                'notes' => 'required|string',
            ]);

            // Temporary user ID
            $userId = 1;

            $stock = $this->stockService->recordDamage($validated, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Damage recorded successfully',
                'data' => $stock,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record damage',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get stock movements.
     */
    public function movements(int $id, Request $request): JsonResponse
    {
        try {
            $limit = $request->query('limit', 50);
            $movements = $this->stockService->getMovements($id, $limit);

            return response()->json([
                'success' => true,
                'message' => 'Stock movements retrieved successfully',
                'data' => $movements,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve stock movements',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recent movements for poktan.
     */
    public function recentMovements(Request $request): JsonResponse
    {
        try {
            $poktanId = $request->query('poktan_id');
            $limit = $request->query('limit', 20);

            if (!$poktanId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Poktan ID is required',
                ], 400);
            }

            $movements = $this->stockService->getRecentMovements($poktanId, $limit);

            return response()->json([
                'success' => true,
                'message' => 'Recent movements retrieved successfully',
                'data' => $movements,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve recent movements',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Transfer stock from poktan to gapoktan.
     */
    public function transferToGapoktan(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'poktan_id' => 'required|exists:poktans,id',
                'commodity_id' => 'required|exists:commodities,id',
                'grade_id' => 'required|exists:commodity_grades,id',
                'quantity' => 'required|numeric|min:0.01',
                'unit' => 'required|string|max:20',
                'from_location' => 'nullable|string|max:255',
                'to_location' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000',
            ]);

            // TODO: Get from auth after authentication is implemented
            $userId = 1;

            $result = $this->stockService->transferToGapoktan($validated, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Stock transferred to gapoktan successfully',
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to transfer stock to gapoktan',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get all gapoktan stocks.
     */
    public function gapoktanStocks(): JsonResponse
    {
        try {
            $result = $this->stockService->getGapoktanStocks();

            return response()->json([
                'success' => true,
                'message' => 'Gapoktan stocks retrieved successfully',
                'data' => $result['stocks'],
                'total_items' => $result['total_items'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve gapoktan stocks',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get gapoktan stock summary.
     */
    public function gapoktanSummary(): JsonResponse
    {
        try {
            $summary = $this->stockService->getGapoktanSummary();

            return response()->json([
                'success' => true,
                'message' => 'Gapoktan stock summary retrieved successfully',
                'data' => $summary,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve gapoktan stock summary',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

