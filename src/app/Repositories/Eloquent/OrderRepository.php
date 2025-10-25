<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Collection;

class OrderRepository implements OrderRepositoryInterface
{
    public function getAll(?string $orderStatus = null, ?string $paymentStatus = null): Collection
    {
        $query = Order::with(['items.product.commodity', 'items.product.grade'])
            ->orderBy('created_at', 'desc');

        if ($orderStatus) {
            $query->where('order_status', $orderStatus);
        }

        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }

        return $query->get();
    }

    public function getPendingOrders(): Collection
    {
        return Order::with(['items.product'])
            ->pending()
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getActiveOrders(): Collection
    {
        return Order::with(['items.product'])
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getCompletedOrders(): Collection
    {
        return Order::with(['items.product'])
            ->completed()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById(int $id): ?Order
    {
        return Order::with(['items.product.commodity', 'items.product.grade', 'items.poktan'])
            ->find($id);
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return Order::with(['items.product.commodity', 'items.product.grade'])
            ->where('order_number', $orderNumber)
            ->first();
    }

    public function getByBuyerPhone(string $phone): Collection
    {
        return Order::with(['items.product'])
            ->where('buyer_phone', $phone)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return Order::where('id', $id)->update($data);
    }

    public function updateStatus(int $id, string $status): bool
    {
        return Order::where('id', $id)->update([
            'order_status' => $status,
            'updated_at' => now(),
        ]);
    }

    public function updatePaymentStatus(int $id, string $status): bool
    {
        return Order::where('id', $id)->update([
            'payment_status' => $status,
            'updated_at' => now(),
        ]);
    }

    public function cancel(int $id, ?string $reason = null): bool
    {
        $data = [
            'order_status' => 'cancelled',
            'updated_at' => now(),
        ];

        if ($reason) {
            $data['notes'] = $reason;
        }

        return Order::where('id', $id)->update($data);
    }

    public function getStatistics(): array
    {
        $total = Order::count();
        $pending = Order::pending()->count();
        $active = Order::active()->count();
        $completed = Order::completed()->count();
        $cancelled = Order::where('order_status', 'cancelled')->count();

        $totalRevenue = Order::where('order_status', 'delivered')
            ->where('payment_status', 'paid')
            ->sum('grand_total');

        $unpaidAmount = Order::whereIn('order_status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->sum('grand_total');

        return [
            'total_orders' => $total,
            'pending' => $pending,
            'active' => $active,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'total_revenue' => $totalRevenue,
            'unpaid_amount' => $unpaidAmount,
        ];
    }
}
