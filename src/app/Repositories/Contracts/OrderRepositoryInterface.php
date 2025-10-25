<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface
{
    /**
     * Get all orders with optional filters
     */
    public function getAll(?string $orderStatus = null, ?string $paymentStatus = null): Collection;

    /**
     * Get pending orders
     */
    public function getPendingOrders(): Collection;

    /**
     * Get active orders (confirmed, processing, shipped)
     */
    public function getActiveOrders(): Collection;

    /**
     * Get completed orders
     */
    public function getCompletedOrders(): Collection;

    /**
     * Find order by ID with items
     */
    public function findById(int $id): ?Order;

    /**
     * Find order by order number
     */
    public function findByOrderNumber(string $orderNumber): ?Order;

    /**
     * Get orders by buyer phone (for tracking)
     */
    public function getByBuyerPhone(string $phone): Collection;

    /**
     * Create new order
     */
    public function create(array $data): Order;

    /**
     * Update order
     */
    public function update(int $id, array $data): bool;

    /**
     * Update order status
     */
    public function updateStatus(int $id, string $status): bool;

    /**
     * Update payment status
     */
    public function updatePaymentStatus(int $id, string $status): bool;

    /**
     * Cancel order
     */
    public function cancel(int $id, ?string $reason = null): bool;

    /**
     * Get order statistics
     */
    public function getStatistics(): array;
}
