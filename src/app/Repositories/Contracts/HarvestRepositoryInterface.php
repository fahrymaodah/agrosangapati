<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface HarvestRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get harvests by poktan ID with relations.
     */
    public function getByPoktan(int $poktanId, array $relations = []): Collection;

    /**
     * Get harvests by member ID.
     */
    public function getByMember(int $memberId, array $relations = []): Collection;

    /**
     * Get harvests by date range.
     */
    public function getByDateRange(string $startDate, string $endDate, ?int $poktanId = null): Collection;

    /**
     * Get harvests by status.
     */
    public function getByStatus(string $status, ?int $poktanId = null): Collection;

    /**
     * Get harvests with full details (all relations).
     */
    public function getWithDetails(int $id): ?object;

    /**
     * Get harvest summary by poktan.
     */
    public function getSummaryByPoktan(int $poktanId): array;

    /**
     * Get total quantity by commodity and grade.
     */
    public function getTotalQuantity(int $commodityId, int $gradeId, ?int $poktanId = null): float;
}
