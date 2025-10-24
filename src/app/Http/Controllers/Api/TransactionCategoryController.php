<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TransactionCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionCategoryController extends Controller
{
    protected TransactionCategoryService $service;

    public function __construct(TransactionCategoryService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of transaction categories.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [];

            // Filter by type (income/expense)
            if ($request->has('type')) {
                $filters['type'] = $request->type;
            }

            // Filter by poktan_id (get available categories for poktan)
            if ($request->has('poktan_id')) {
                $filters['poktan_id'] = $request->poktan_id;
            }

            // Filter default only
            if ($request->has('default_only')) {
                $filters['default_only'] = $request->boolean('default_only');
            }

            $categories = $this->service->getAllCategories($filters);

            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created transaction category.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'is_default' => 'boolean',
            'poktan_id' => 'nullable|exists:poktans,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->service->createCategory($validator->validated());

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * Display the specified transaction category.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $category = $this->service->getCategoryById($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Category retrieved successfully',
                'data' => $category,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified transaction category.
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:income,expense',
            'is_default' => 'sometimes|boolean',
            'poktan_id' => 'nullable|exists:poktans,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->service->updateCategory($id, $validator->validated());

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Remove the specified transaction category.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->service->deleteCategory($id);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get income categories for a poktan.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function income(Request $request): JsonResponse
    {
        try {
            $poktanId = $request->query('poktan_id');

            if (!$poktanId) {
                return response()->json([
                    'success' => false,
                    'message' => 'poktan_id is required',
                ], 400);
            }

            $categories = $this->service->getIncomeCategories($poktanId);

            return response()->json([
                'success' => true,
                'message' => 'Income categories retrieved successfully',
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve income categories: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get expense categories for a poktan.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function expense(Request $request): JsonResponse
    {
        try {
            $poktanId = $request->query('poktan_id');

            if (!$poktanId) {
                return response()->json([
                    'success' => false,
                    'message' => 'poktan_id is required',
                ], 400);
            }

            $categories = $this->service->getExpenseCategories($poktanId);

            return response()->json([
                'success' => true,
                'message' => 'Expense categories retrieved successfully',
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve expense categories: ' . $e->getMessage(),
            ], 500);
        }
    }
}

