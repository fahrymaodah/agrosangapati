<?php

namespace App\Repositories\Eloquent;

use App\Models\Shipment;
use App\Repositories\Contracts\ShipmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ShipmentRepository implements ShipmentRepositoryInterface
{
    protected $model;

    public function __construct(Shipment $model)
    {
        $this->model = $model;
    }

    public function getAllShipments(array $filters = [])
    {
        $query = $this->model->with(['order' => function ($query) {
            $query->select('id', 'order_number', 'buyer_name', 'buyer_phone', 'order_status');
        }]);

        // Filter by shipment status
        if (isset($filters['status'])) {
            $query->where('shipment_status', $filters['status']);
        }

        // Filter by courier
        if (isset($filters['courier'])) {
            $query->where('courier_name', 'like', '%' . $filters['courier'] . '%');
        }

        // Filter by date range
        if (isset($filters['from_date'])) {
            $query->whereDate('shipping_date', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->whereDate('shipping_date', '<=', $filters['to_date']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getShipmentById(int $id)
    {
        return $this->model->with(['order.items.product'])->findOrFail($id);
    }

    public function getShipmentByOrderId(int $orderId)
    {
        return $this->model->where('order_id', $orderId)->first();
    }

    public function getShipmentByTrackingNumber(string $trackingNumber)
    {
        return $this->model->with(['order.items.product'])
            ->where('tracking_number', $trackingNumber)
            ->firstOrFail();
    }

    public function createShipment(array $data)
    {
        return $this->model->create($data);
    }

    public function updateShipment(int $id, array $data)
    {
        $shipment = $this->model->findOrFail($id);
        $shipment->update($data);
        return $shipment->fresh(['order']);
    }

    public function deleteShipment(int $id)
    {
        $shipment = $this->model->findOrFail($id);
        return $shipment->delete();
    }

    public function getShipmentsInProgress()
    {
        return $this->model->with(['order'])
            ->whereIn('shipment_status', ['picked_up', 'in_transit'])
            ->orderBy('shipping_date', 'asc')
            ->get();
    }

    public function getLateShipments()
    {
        return $this->model->with(['order'])
            ->whereNotIn('shipment_status', ['delivered'])
            ->whereNotNull('estimated_arrival')
            ->whereDate('estimated_arrival', '<', now())
            ->orderBy('estimated_arrival', 'asc')
            ->get();
    }

    public function getShipmentStatistics()
    {
        return [
            'total_shipments' => $this->model->count(),
            'preparing' => $this->model->where('shipment_status', 'preparing')->count(),
            'picked_up' => $this->model->where('shipment_status', 'picked_up')->count(),
            'in_transit' => $this->model->where('shipment_status', 'in_transit')->count(),
            'delivered' => $this->model->where('shipment_status', 'delivered')->count(),
            'in_progress' => $this->model->whereIn('shipment_status', ['picked_up', 'in_transit'])->count(),
            'late_shipments' => $this->model
                ->whereNotIn('shipment_status', ['delivered'])
                ->whereNotNull('estimated_arrival')
                ->whereDate('estimated_arrival', '<', now())
                ->count(),
        ];
    }

    public function getShipmentsByCourier(string $courier)
    {
        return $this->model->with(['order'])
            ->where('courier_name', $courier)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
