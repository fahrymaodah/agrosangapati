<?php

namespace App\Repositories\Contracts;

interface SalesDistributionRepositoryInterface
{
    public function getAllDistributions(array $filters = []);
    public function getDistributionById(int $id);
    public function getDistributionsByOrderId(int $orderId);
    public function getDistributionsByPoktanId(int $poktanId, array $filters = []);
    public function getPendingDistributions(array $filters = []);
    public function getPaidDistributions(array $filters = []);
    public function createDistribution(array $data);
    public function updateDistribution(int $id, array $data);
    public function markAsPaid(int $id, string $paidAt);
    public function getStatistics(?int $poktanId = null);
    public function getTotalPendingByPoktan(int $poktanId);
    public function checkExistingDistribution(int $orderItemId, int $poktanId): bool;
}
