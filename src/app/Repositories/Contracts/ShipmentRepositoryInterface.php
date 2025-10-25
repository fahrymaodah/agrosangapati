<?php

namespace App\Repositories\Contracts;

interface ShipmentRepositoryInterface
{
    public function getAllShipments(array $filters = []);
    public function getShipmentById(int $id);
    public function getShipmentByOrderId(int $orderId);
    public function getShipmentByTrackingNumber(string $trackingNumber);
    public function createShipment(array $data);
    public function updateShipment(int $id, array $data);
    public function deleteShipment(int $id);
    public function getShipmentsInProgress();
    public function getLateShipments();
    public function getShipmentStatistics();
    public function getShipmentsByCourier(string $courier);
}
