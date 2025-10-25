<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    protected Product $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    /**
     * Get all products with optional relations.
     */
    public function getAll(array $relations = []): Collection
    {
        return $this->model
            ->with($relations)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get public products (available or pre_order).
     */
    public function getPublicProducts(array $relations = []): Collection
    {
        return $this->model
            ->with($relations)
            ->public()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get available products only (in stock).
     */
    public function getAvailableProducts(array $relations = []): Collection
    {
        return $this->model
            ->with($relations)
            ->available()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Find product by ID.
     */
    public function findById(int $id, array $relations = []): ?Product
    {
        return $this->model
            ->with($relations)
            ->find($id);
    }

    /**
     * Search products by name or description.
     */
    public function search(string $query, array $relations = []): Collection
    {
        return $this->model
            ->with($relations)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get products by commodity.
     */
    public function getByCommodity(int $commodityId, array $relations = []): Collection
    {
        return $this->model
            ->with($relations)
            ->where('commodity_id', $commodityId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get products by status.
     */
    public function getByStatus(string $status, array $relations = []): Collection
    {
        return $this->model
            ->with($relations)
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get popular products (by views count).
     */
    public function getPopularProducts(int $limit = 10, array $relations = []): Collection
    {
        return $this->model
            ->with($relations)
            ->public()
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Create a new product.
     */
    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    /**
     * Update a product.
     */
    public function update(int $id, array $data): bool
    {
        $product = $this->model->findOrFail($id);
        return $product->update($data);
    }

    /**
     * Delete a product.
     */
    public function delete(int $id): bool
    {
        $product = $this->model->findOrFail($id);
        return $product->delete();
    }

    /**
     * Update stock quantity.
     */
    public function updateStock(int $id, float $quantity): bool
    {
        $product = $this->model->findOrFail($id);
        return $product->update(['stock_quantity' => $quantity]);
    }

    /**
     * Increment views count.
     */
    public function incrementViews(int $id): bool
    {
        $product = $this->model->find($id);
        if ($product) {
            $product->incrementViews();
            return true;
        }
        return false;
    }
}
