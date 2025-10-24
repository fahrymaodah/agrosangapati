<?php

namespace App\Services;

use App\Repositories\TransactionCategoryRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionCategoryService
{
    protected TransactionCategoryRepository $repository;

    public function __construct(TransactionCategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all categories with filters.
     */
    public function getAllCategories(array $filters = []): Collection
    {
        return $this->repository->getAll($filters);
    }

    /**
     * Get a category by ID.
     */
    public function getCategoryById(int $id): ?object
    {
        return $this->repository->find($id);
    }

    /**
     * Get available categories for a poktan (default + custom).
     */
    public function getAvailableCategoriesForPoktan(int $poktanId, ?string $type = null): Collection
    {
        $filters = ['poktan_id' => $poktanId];
        
        if ($type) {
            $filters['type'] = $type;
        }

        return $this->repository->getAll($filters);
    }

    /**
     * Create a new category.
     */
    public function createCategory(array $data): array
    {
        try {
            DB::beginTransaction();

            // Validate: default categories cannot have poktan_id
            if (isset($data['is_default']) && $data['is_default'] && isset($data['poktan_id'])) {
                return [
                    'success' => false,
                    'message' => 'Default categories cannot be assigned to a specific poktan',
                ];
            }

            // Validate: custom categories must have poktan_id
            if ((!isset($data['is_default']) || !$data['is_default']) && !isset($data['poktan_id'])) {
                return [
                    'success' => false,
                    'message' => 'Custom categories must be assigned to a poktan',
                ];
            }

            // Check duplicate name in same scope
            if ($this->isDuplicateName($data['name'], $data['type'], $data['poktan_id'] ?? null)) {
                return [
                    'success' => false,
                    'message' => 'Category with this name already exists',
                ];
            }

            $category = $this->repository->create($data);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create category: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to create category: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update a category.
     */
    public function updateCategory(int $id, array $data): array
    {
        try {
            DB::beginTransaction();

            $category = $this->repository->find($id);

            if (!$category) {
                return [
                    'success' => false,
                    'message' => 'Category not found',
                ];
            }

            // Prevent updating default categories' poktan_id
            if ($category->is_default && isset($data['poktan_id']) && $data['poktan_id']) {
                return [
                    'success' => false,
                    'message' => 'Cannot assign default category to a specific poktan',
                ];
            }

            // Check duplicate name if name is being changed
            if (isset($data['name']) && $data['name'] !== $category->name) {
                $type = $data['type'] ?? $category->type;
                $poktanId = $data['poktan_id'] ?? $category->poktan_id;
                
                if ($this->isDuplicateName($data['name'], $type, $poktanId, $id)) {
                    return [
                        'success' => false,
                        'message' => 'Category with this name already exists',
                    ];
                }
            }

            $updated = $this->repository->update($id, $data);

            if (!$updated) {
                throw new \Exception('Failed to update category');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $this->repository->find($id),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update category: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to update category: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a category.
     */
    public function deleteCategory(int $id): array
    {
        try {
            $category = $this->repository->find($id);

            if (!$category) {
                return [
                    'success' => false,
                    'message' => 'Category not found',
                ];
            }

            // Prevent deleting default categories
            if ($category->is_default) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete default category',
                ];
            }

            // Prevent deleting if used in transactions
            if ($this->repository->isUsed($id)) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete category that is being used in transactions',
                ];
            }

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Failed to delete category');
            }

            return [
                'success' => true,
                'message' => 'Category deleted successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete category: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to delete category: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get income categories for a poktan.
     */
    public function getIncomeCategories(int $poktanId): Collection
    {
        return $this->repository->getIncomeCategories($poktanId);
    }

    /**
     * Get expense categories for a poktan.
     */
    public function getExpenseCategories(int $poktanId): Collection
    {
        return $this->repository->getExpenseCategories($poktanId);
    }

    /**
     * Get default categories.
     */
    public function getDefaultCategories(): Collection
    {
        return $this->repository->getDefaultCategories();
    }

    /**
     * Get custom categories for a poktan.
     */
    public function getCustomCategories(int $poktanId): Collection
    {
        return $this->repository->getCustomCategories($poktanId);
    }

    /**
     * Check if category name is duplicate.
     */
    private function isDuplicateName(string $name, string $type, ?int $poktanId, ?int $excludeId = null): bool
    {
        $query = DB::table('transaction_categories')
            ->where('name', $name)
            ->where('type', $type);

        if ($poktanId) {
            $query->where('poktan_id', $poktanId);
        } else {
            $query->whereNull('poktan_id');
        }

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
