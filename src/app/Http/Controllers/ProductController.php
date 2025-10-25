<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Get all products (admin/gapoktan).
     */
    public function index(): JsonResponse
    {
        try {
            $products = $this->productService->getAllProducts();
            
            return response()->json([
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get public product catalog (for customers).
     */
    public function catalog(): JsonResponse
    {
        try {
            $products = $this->productService->getPublicCatalog();
            
            return response()->json([
                'success' => true,
                'message' => 'Product catalog retrieved successfully',
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve catalog',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available products (in stock).
     */
    public function available(): JsonResponse
    {
        try {
            $products = $this->productService->getAvailableProducts();
            
            return response()->json([
                'success' => true,
                'message' => 'Available products retrieved successfully',
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available products',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search products.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->input('q', '');
            
            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query is required',
                ], 400);
            }

            $products = $this->productService->searchProducts($query);
            
            return response()->json([
                'success' => true,
                'message' => 'Search completed successfully',
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get products by commodity.
     */
    public function byCommodity(int $commodityId): JsonResponse
    {
        try {
            $products = $this->productService->getProductsByCommodity($commodityId);
            
            return response()->json([
                'success' => true,
                'message' => 'Products by commodity retrieved successfully',
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get products by status.
     */
    public function byStatus(string $status): JsonResponse
    {
        try {
            $products = $this->productService->getProductsByStatus($status);
            
            return response()->json([
                'success' => true,
                'message' => "Products with status '{$status}' retrieved successfully",
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get popular products.
     */
    public function popular(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);
            $products = $this->productService->getPopularProducts($limit);
            
            return response()->json([
                'success' => true,
                'message' => 'Popular products retrieved successfully',
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve popular products',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get product statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->productService->getProductStatistics();
            
            return response()->json([
                'success' => true,
                'message' => 'Product statistics retrieved successfully',
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show product detail.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $incrementViews = $request->input('increment_views', false);
            $product = $this->productService->getProductById($id, $incrementViews);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Product retrieved successfully',
                'data' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new product.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'commodity_id' => 'required|exists:commodities,id',
                'grade_id' => 'required|exists:commodity_grades,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock_quantity' => 'nullable|numeric|min:0',
                'unit' => 'required|string|max:20',
                'minimum_order' => 'nullable|numeric|min:0.01',
                'product_photos.*' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
                'status' => 'nullable|in:available,pre_order,sold_out,inactive',
                'created_by' => 'required|exists:users,id',
            ]);

            // Handle file uploads
            if ($request->hasFile('product_photos')) {
                $validated['product_photos'] = $request->file('product_photos');
            }

            $product = $this->productService->createProduct($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a product.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'commodity_id' => 'sometimes|exists:commodities,id',
                'grade_id' => 'sometimes|exists:commodity_grades,id',
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'price' => 'sometimes|numeric|min:0',
                'stock_quantity' => 'sometimes|numeric|min:0',
                'unit' => 'sometimes|string|max:20',
                'minimum_order' => 'sometimes|numeric|min:0.01',
                'product_photos.*' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
                'status' => 'sometimes|in:available,pre_order,sold_out,inactive',
            ]);

            // Handle file uploads
            if ($request->hasFile('product_photos')) {
                $validated['product_photos'] = $request->file('product_photos');
            }

            $success = $this->productService->updateProduct($id, $validated);
            
            if ($success) {
                $product = $this->productService->getProductById($id);
                return response()->json([
                    'success' => true,
                    'message' => 'Product updated successfully',
                    'data' => $product,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update product',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a product.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $success = $this->productService->deleteProduct($id);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product deleted successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update product status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:available,pre_order,sold_out,inactive',
            ]);

            $success = $this->productService->updateStatus($id, $validated['status']);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product status updated successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update product status',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync product stock with gapoktan stock.
     */
    public function syncStock(int $id): JsonResponse
    {
        try {
            $result = $this->productService->syncStockWithGapoktan($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Product stock synced successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync product stock',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
