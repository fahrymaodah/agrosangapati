<?php

namespace App\Repositories;

use App\Models\TransactionCategory;
use Illuminate\Database\Eloquent\Collection;

class TransactionCategoryRepository
{
    /**
     * Get all categories with optional filters.
     */
    public function getAll(array $filters = []): Collection
    {
        $query = TransactionCategory::query();

        // Filter by type (income/expense)
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filter by poktan_id or get available categories for poktan
        if (isset($filters['poktan_id'])) {
            $query->availableForPoktan($filters['poktan_id']);
        }

        // Filter default only
        if (isset($filters['default_only']) && $filters['default_only']) {
            $query->default();
        }

        return $query->orderBy('type')->orderBy('name')->get();
    }

    /**
     * Find a category by ID.
     */
    public function find(int $id): ?TransactionCategory
    {
        return TransactionCategory::find($id);
    }

    /**
     * Find a category by name.
     */
    public function findByName(string $name, ?int $poktanId = null): ?TransactionCategory
    {
        $query = TransactionCategory::where('name', $name);
        
        if ($poktanId) {
            $query->where(function($q) use ($poktanId) {
                $q->whereNull('poktan_id')
                  ->orWhere('poktan_id', $poktanId);
            });
        } else {
            $query->whereNull('poktan_id');
        }
        
        return $query->first();
    }

    /**
     * Create a new category.
     */
    public function create(array $data): TransactionCategory
    {
        return TransactionCategory::create($data);
    }

    /**
     * Update a category.
     */
    public function update(int $id, array $data): bool
    {
        $category = $this->find($id);
        
        if (!$category) {
            return false;
        }

        return $category->update($data);
    }

    /**
     * Delete a category.
     */
    public function delete(int $id): bool
    {
        $category = $this->find($id);
        
        if (!$category) {
            return false;
        }

        return $category->delete();
    }

    /**
     * Get income categories for a poktan.
     */
    public function getIncomeCategories(int $poktanId): Collection
    {
        return TransactionCategory::income()
            ->availableForPoktan($poktanId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get expense categories for a poktan.
     */
    public function getExpenseCategories(int $poktanId): Collection
    {
        return TransactionCategory::expense()
            ->availableForPoktan($poktanId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get default categories only.
     */
    public function getDefaultCategories(): Collection
    {
        return TransactionCategory::default()
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get custom categories for a specific poktan.
     */
    public function getCustomCategories(int $poktanId): Collection
    {
        return TransactionCategory::forPoktan($poktanId)
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if category is being used in transactions.
     */
    public function isUsed(int $id): bool
    {
        $category = $this->find($id);
        
        if (!$category) {
            return false;
        }

        return $category->transactions()->exists();
    }
}
