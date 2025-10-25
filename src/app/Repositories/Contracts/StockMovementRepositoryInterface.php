<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface StockMovementRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get movements by stock ID.
     */
    public function getByStock(int $stockId, int $limit = 50): Collection;

    /**
     * Get movements by type.
     */
    public function getByType(int $stockId, string $type): Collection;

    /**
     * Get movements by date range.
     */
    public function getByDateRange(int $stockId, string $startDate, string $endDate): Collection;

    /**
     * Get recent movements for poktan.
     */
    public function getRecentByPoktan(int $poktanId, int $limit = 20): Collection;
}
