<?php

namespace App\Services;

use App\Repositories\Contracts\ShipmentRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ShipmentService
{
    protected $shipmentRepository;
    protected $orderRepository;

    public function __construct(
        ShipmentRepositoryInterface $shipmentRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Get all shipments with filters
     */
    public function getAllShipments(array $filters = [])
    {
        return $this->shipmentRepository->getAllShipments($filters);
    }

    /**
     * Get shipment by ID
     */
    public function getShipmentById(int $id)
    {
        return $this->shipmentRepository->getShipmentById($id);
    }

    /**
     * Get shipment by order ID
     */
    public function getShipmentByOrderId(int $orderId)
    {
        return $this->shipmentRepository->getShipmentByOrderId($orderId);
    }

    /**
     * Track shipment by tracking number (public)
     */
    public function trackShipment(string $trackingNumber)
    {
        return $this->shipmentRepository->getShipmentByTrackingNumber($trackingNumber);
    }

    /**
     * Create shipment for an order
     */
    public function createShipment(int $orderId, array $data)
    {
        // Validate order exists and is ready for shipment
        $order = $this->orderRepository->findById($orderId);
        
        if (!$order) {
            throw new \Exception("Order not found.");
        }
        
        if (!in_array($order->order_status, ['confirmed', 'processing', 'shipped'])) {
            throw new \Exception("Order must be confirmed or processing before creating shipment.");
        }

        // Check if shipment already exists
        $existingShipment = $this->shipmentRepository->getShipmentByOrderId($orderId);
        if ($existingShipment) {
            throw new \Exception("Shipment already exists for this order.");
        }

        return DB::transaction(function () use ($orderId, $data, $order) {
            // Create shipment
            $shipmentData = array_merge($data, ['order_id' => $orderId]);
            $shipment = $this->shipmentRepository->createShipment($shipmentData);

            // Update order status to shipped if not already
            if ($order->order_status !== 'shipped') {
                $this->orderRepository->update($orderId, [
                    'order_status' => 'shipped',
                ]);
            }

            return $shipment->load(['order']);
        });
    }

    /**
     * Update shipment information
     */
    public function updateShipment(int $id, array $data)
    {
        $shipment = $this->shipmentRepository->getShipmentById($id);

        if (!$shipment->canBeUpdated()) {
            throw new \Exception("Cannot update delivered shipment.");
        }

        return DB::transaction(function () use ($id, $data, $shipment) {
            $updatedShipment = $this->shipmentRepository->updateShipment($id, $data);

            // If shipment status changed to delivered, update order status
            if (isset($data['shipment_status']) && $data['shipment_status'] === 'delivered') {
                $this->orderRepository->update($shipment->order_id, [
                    'order_status' => 'delivered',
                ]);

                // Set actual arrival date if not provided
                if (!isset($data['actual_arrival'])) {
                    $this->shipmentRepository->updateShipment($id, [
                        'actual_arrival' => now()->toDateString(),
                    ]);
                }
            }

            return $updatedShipment->fresh(['order']);
        });
    }

    /**
     * Mark shipment as picked up
     */
    public function markAsPickedUp(int $id, ?string $notes = null)
    {
        $data = ['shipment_status' => 'picked_up'];
        
        if ($notes) {
            $shipment = $this->shipmentRepository->getShipmentById($id);
            $data['notes'] = $this->appendNotes($shipment->notes, $notes, 'Picked Up');
        }

        return $this->updateShipment($id, $data);
    }

    /**
     * Mark shipment as in transit
     */
    public function markAsInTransit(int $id, ?string $notes = null)
    {
        $data = ['shipment_status' => 'in_transit'];
        
        if ($notes) {
            $shipment = $this->shipmentRepository->getShipmentById($id);
            $data['notes'] = $this->appendNotes($shipment->notes, $notes, 'In Transit');
        }

        return $this->updateShipment($id, $data);
    }

    /**
     * Mark shipment as delivered
     */
    public function markAsDelivered(int $id, ?string $notes = null)
    {
        $data = [
            'shipment_status' => 'delivered',
            'actual_arrival' => now()->toDateString(),
        ];
        
        if ($notes) {
            $shipment = $this->shipmentRepository->getShipmentById($id);
            $data['notes'] = $this->appendNotes($shipment->notes, $notes, 'Delivered');
        }

        return $this->updateShipment($id, $data);
    }

    /**
     * Update tracking number
     */
    public function updateTrackingNumber(int $id, string $trackingNumber)
    {
        return $this->shipmentRepository->updateShipment($id, [
            'tracking_number' => $trackingNumber,
        ]);
    }

    /**
     * Update courier information
     */
    public function updateCourier(int $id, string $courierName)
    {
        return $this->shipmentRepository->updateShipment($id, [
            'courier_name' => $courierName,
        ]);
    }

    /**
     * Upload proof of delivery photo
     */
    public function uploadProofPhoto(int $id, string $photoPath)
    {
        return $this->shipmentRepository->updateShipment($id, [
            'proof_photo' => $photoPath,
        ]);
    }

    /**
     * Get shipments in progress
     */
    public function getShipmentsInProgress()
    {
        return $this->shipmentRepository->getShipmentsInProgress();
    }

    /**
     * Get late shipments
     */
    public function getLateShipments()
    {
        return $this->shipmentRepository->getLateShipments();
    }

    /**
     * Get shipment statistics
     */
    public function getShipmentStatistics()
    {
        return $this->shipmentRepository->getShipmentStatistics();
    }

    /**
     * Get shipments by courier
     */
    public function getShipmentsByCourier(string $courier)
    {
        return $this->shipmentRepository->getShipmentsByCourier($courier);
    }

    /**
     * Delete shipment
     */
    public function deleteShipment(int $id)
    {
        $shipment = $this->shipmentRepository->getShipmentById($id);

        if ($shipment->shipment_status === 'delivered') {
            throw new \Exception("Cannot delete delivered shipment.");
        }

        return $this->shipmentRepository->deleteShipment($id);
    }

    /**
     * Append notes with context marker
     */
    private function appendNotes(?string $existingNotes, string $newNotes, string $context): string
    {
        $marker = "[{$context}]";
        $timestamp = now()->format('Y-m-d H:i');
        
        if ($existingNotes) {
            return $existingNotes . "\n\n{$marker} ({$timestamp}) {$newNotes}";
        }
        
        return "{$marker} ({$timestamp}) {$newNotes}";
    }
}
