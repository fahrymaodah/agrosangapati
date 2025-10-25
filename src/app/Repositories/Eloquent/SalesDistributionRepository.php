<?php

namespace App\Repositories\Eloquent;

use App\Models\SalesDistribution;
use App\Repositories\Contracts\SalesDistributionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SalesDistributionRepository implements SalesDistributionRepositoryInterface
{
    protected $model;

    public function __construct(SalesDistribution $model)
    {
        $this->model = $model;
    }

    public function getAllDistributions(array $filters = [])
    {
        $query = $this->model->with(['orderItem.product', 'poktan', 'commodity']);

        // Filter by poktan
        if (isset($filters['poktan_id'])) {
            $query->where('poktan_id', $filters['poktan_id']);
        }

        // Filter by payment status
        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        // Filter by date range
        if (isset($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        // Filter by paid date range
        if (isset($filters['paid_start_date'])) {
            $query->whereDate('paid_at', '>=', $filters['paid_start_date']);
        }

        if (isset($filters['paid_end_date'])) {
            $query->whereDate('paid_at', '<=', $filters['paid_end_date']);
        }

        return $query->latest()->get();
    }

    public function getDistributionById(int $id)
    {
        return $this->model->with(['orderItem.product', 'orderItem.order', 'poktan', 'commodity'])->findOrFail($id);
    }

    public function getDistributionsByOrderId(int $orderId)
    {
        return $this->model->with(['orderItem.product', 'poktan', 'commodity'])
            ->whereHas('orderItem', function ($query) use ($orderId) {
                $query->where('order_id', $orderId);
            })
            ->get();
    }

    public function getDistributionsByPoktanId(int $poktanId, array $filters = [])
    {
        $query = $this->model->with(['orderItem.product', 'orderItem.order', 'commodity'])
            ->where('poktan_id', $poktanId);

        // Filter by payment status
        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        // Filter by date range
        if (isset($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query->latest()->get();
    }

    public function getPendingDistributions(array $filters = [])
    {
        $query = $this->model->with(['orderItem.product', 'orderItem.order', 'poktan', 'commodity'])
            ->where('payment_status', 'pending');

        // Filter by poktan
        if (isset($filters['poktan_id'])) {
            $query->where('poktan_id', $filters['poktan_id']);
        }

        return $query->latest()->get();
    }

    public function getPaidDistributions(array $filters = [])
    {
        $query = $this->model->with(['orderItem.product', 'orderItem.order', 'poktan', 'commodity'])
            ->where('payment_status', 'paid');

        // Filter by poktan
        if (isset($filters['poktan_id'])) {
            $query->where('poktan_id', $filters['poktan_id']);
        }

        // Filter by paid date range
        if (isset($filters['paid_start_date'])) {
            $query->whereDate('paid_at', '>=', $filters['paid_start_date']);
        }

        if (isset($filters['paid_end_date'])) {
            $query->whereDate('paid_at', '<=', $filters['paid_end_date']);
        }

        return $query->latest('paid_at')->get();
    }

    public function createDistribution(array $data)
    {
        return $this->model->create($data);
    }

    public function updateDistribution(int $id, array $data)
    {
        $distribution = $this->getDistributionById($id);
        $distribution->update($data);
        return $distribution->fresh();
    }

    public function markAsPaid(int $id, string $paidAt)
    {
        $distribution = $this->getDistributionById($id);
        $distribution->update([
            'payment_status' => 'paid',
            'paid_at' => $paidAt,
        ]);
        return $distribution->fresh();
    }

    public function getStatistics(?int $poktanId = null)
    {
        $query = $this->model->query();

        if ($poktanId) {
            $query->where('poktan_id', $poktanId);
        }

        return [
            'total_distributions' => $query->count(),
            'pending_count' => (clone $query)->where('payment_status', 'pending')->count(),
            'paid_count' => (clone $query)->where('payment_status', 'paid')->count(),
            'total_revenue' => (clone $query)->sum('total_revenue'),
            'total_margin' => (clone $query)->sum('gapoktan_margin'),
            'total_poktan_payment' => (clone $query)->sum('poktan_payment'),
            'pending_payment_amount' => (clone $query)->where('payment_status', 'pending')->sum('poktan_payment'),
            'paid_payment_amount' => (clone $query)->where('payment_status', 'paid')->sum('poktan_payment'),
        ];
    }

    public function getTotalPendingByPoktan(int $poktanId)
    {
        return $this->model->where('poktan_id', $poktanId)
            ->where('payment_status', 'pending')
            ->sum('poktan_payment');
    }

    public function checkExistingDistribution(int $orderItemId, int $poktanId): bool
    {
        return $this->model->where('order_item_id', $orderItemId)
            ->where('poktan_id', $poktanId)
            ->exists();
    }
}
