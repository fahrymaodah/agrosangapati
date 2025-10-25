<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface ProductionReportRepositoryInterface
{
    /**
     * Get member's harvest history.
     */
    public function getMemberHarvestHistory(int $memberId, ?string $startDate = null, ?string $endDate = null): Collection;

    /**
     * Get member's production summary.
     */
    public function getMemberProductionSummary(int $memberId, ?string $startDate = null, ?string $endDate = null): array;

    /**
     * Get member's production by commodity.
     */
    public function getMemberProductionByCommodity(int $memberId, ?string $startDate = null, ?string $endDate = null): Collection;

    /**
     * Get member's production comparison between two periods.
     */
    public function getMemberProductionComparison(int $memberId, string $currentStart, string $currentEnd, string $previousStart, string $previousEnd): array;

    /**
     * Get top producing members in a poktan.
     */
    public function getTopProducers(int $poktanId, int $limit = 10, ?string $startDate = null, ?string $endDate = null): Collection;

    /**
     * Get poktan's production summary.
     */
    public function getPoktanProductionSummary(int $poktanId, ?string $startDate = null, ?string $endDate = null): array;

    /**
     * Get poktan's production by commodity.
     */
    public function getPoktanProductionByCommodity(int $poktanId, ?string $startDate = null, ?string $endDate = null): Collection;

    /**
     * Get poktan's production breakdown by member.
     */
    public function getPoktanProductionByMember(int $poktanId, ?string $startDate = null, ?string $endDate = null): Collection;

    /**
     * Get poktan's monthly production trend.
     */
    public function getPoktanMonthlyTrend(int $poktanId, int $months = 12): Collection;

    /**
     * Get gapoktan's production summary (all poktans).
     */
    public function getGapoktanProductionSummary(int $gapoktanId, ?string $startDate = null, ?string $endDate = null): array;

    /**
     * Get gapoktan's production by commodity.
     */
    public function getGapoktanProductionByCommodity(int $gapoktanId, ?string $startDate = null, ?string $endDate = null): Collection;

    /**
     * Get gapoktan's production breakdown by poktan.
     */
    public function getGapoktanProductionByPoktan(int $gapoktanId, ?string $startDate = null, ?string $endDate = null): Collection;

    /**
     * Get gapoktan's poktan comparison/ranking.
     */
    public function getGapoktanPoktanComparison(int $gapoktanId, ?string $startDate = null, ?string $endDate = null): Collection;

    /**
     * Get gapoktan's monthly production trend.
     */
    public function getGapoktanMonthlyTrend(int $gapoktanId, int $months = 12): Collection;
}
