<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface StockRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get stock by poktan ID.
     * If poktanId is null, get gapoktan stocks.
     */
    public function getByPoktan(?int $poktanId, array $relations = []): Collection;

    /**
     * Get stock by commodity and grade.
     * If poktanId is null, get from gapoktan stocks.
     */
    public function getByCommodityGrade(?int $poktanId, int $commodityId, int $gradeId, ?string $location = null);

    /**
     * Get low stock items.
     */
    public function getLowStock(int $poktanId, float $minimumQuantity = 100): Collection;

    /**
     * Get stock by location.
     */
    public function getByLocation(int $poktanId, ?string $location): Collection;

    /**
     * Get stock summary by poktan.
     * If poktanId is null, get gapoktan summary.
     */
    public function getSummaryByPoktan(?int $poktanId): array;

    /**
     * Update or create stock.
     */
    public function updateOrCreateStock(array $conditions, array $data);
}
