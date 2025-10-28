<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Activitylog\Models\Activity;

interface ActivityLogRepositoryInterface
{
    /**
     * Get all activity logs with pagination.
     */
    public function getAllPaginated(int $perPage = 20): LengthAwarePaginator;

    /**
     * Get activity log by ID.
     */
    public function getById(int $id): ?Activity;

    /**
     * Get activity logs by causer (user who performed the action).
     */
    public function getByCauser(int $causerId, int $perPage = 20): LengthAwarePaginator;

    /**
     * Get activity logs by subject (the model that was acted upon).
     */
    public function getBySubject(string $subjectType, int $subjectId, int $perPage = 20): LengthAwarePaginator;

    /**
     * Get activity logs by model type.
     */
    public function getByModelType(string $modelType, int $perPage = 20): LengthAwarePaginator;

    /**
     * Get activity logs by event (created, updated, deleted).
     */
    public function getByEvent(string $event, int $perPage = 20): LengthAwarePaginator;

    /**
     * Get activity logs within date range.
     */
    public function getByDateRange(string $startDate, string $endDate, int $perPage = 20): LengthAwarePaginator;

    /**
     * Get recent activity logs.
     */
    public function getRecent(int $limit = 10): Collection;

    /**
     * Search activity logs by description.
     */
    public function search(string $query, int $perPage = 20): LengthAwarePaginator;

    /**
     * Filter activity logs with multiple criteria.
     */
    public function filter(array $filters, int $perPage = 20): LengthAwarePaginator;

    /**
     * Get activity statistics.
     */
    public function getStatistics(): array;

    /**
     * Delete old activity logs.
     */
    public function deleteOlderThan(int $days): int;
}
