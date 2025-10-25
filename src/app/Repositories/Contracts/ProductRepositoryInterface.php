<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Product;

interface ProductRepositoryInterface
{
    /**
     * Get all products with optional relations.
     */
    public function getAll(array $relations = []): Collection;

    /**
     * Get public products (available or pre_order).
     */
    public function getPublicProducts(array $relations = []): Collection;

    /**
     * Get available products only (in stock).
     */
    public function getAvailableProducts(array $relations = []): Collection;

    /**
     * Find product by ID.
     */
    public function findById(int $id, array $relations = []): ?Product;

    /**
     * Search products by name or description.
     */
    public function search(string $query, array $relations = []): Collection;

    /**
     * Get products by commodity.
     */
    public function getByCommodity(int $commodityId, array $relations = []): Collection;

    /**
     * Get products by status.
     */
    public function getByStatus(string $status, array $relations = []): Collection;

    /**
     * Get popular products (by views count).
     */
    public function getPopularProducts(int $limit = 10, array $relations = []): Collection;

    /**
     * Create a new product.
     */
    public function create(array $data): Product;

    /**
     * Update a product.
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a product.
     */
    public function delete(int $id): bool;

    /**
     * Update stock quantity.
     */
    public function updateStock(int $id, float $quantity): bool;

    /**
     * Increment views count.
     */
    public function incrementViews(int $id): bool;
}
