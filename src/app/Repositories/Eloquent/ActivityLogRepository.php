<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Activitylog\Models\Activity;

class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    /**
     * Get all activity logs with pagination.
     */
    public function getAllPaginated(int $perPage = 20): LengthAwarePaginator
    {
        return Activity::with(['causer', 'subject'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get activity log by ID.
     */
    public function getById(int $id): ?Activity
    {
        return Activity::with(['causer', 'subject'])->find($id);
    }

    /**
     * Get activity logs by causer (user who performed the action).
     */
    public function getByCauser(int $causerId, int $perPage = 20): LengthAwarePaginator
    {
        return Activity::with(['causer', 'subject'])
            ->where('causer_type', 'App\Models\User')
            ->where('causer_id', $causerId)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get activity logs by subject (the model that was acted upon).
     */
    public function getBySubject(string $subjectType, int $subjectId, int $perPage = 20): LengthAwarePaginator
    {
        return Activity::with(['causer', 'subject'])
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get activity logs by model type.
     */
    public function getByModelType(string $modelType, int $perPage = 20): LengthAwarePaginator
    {
        return Activity::with(['causer', 'subject'])
            ->where('subject_type', $modelType)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get activity logs by event (created, updated, deleted).
     */
    public function getByEvent(string $event, int $perPage = 20): LengthAwarePaginator
    {
        return Activity::with(['causer', 'subject'])
            ->where('event', $event)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get activity logs within date range.
     */
    public function getByDateRange(string $startDate, string $endDate, int $perPage = 20): LengthAwarePaginator
    {
        return Activity::with(['causer', 'subject'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get recent activity logs.
     */
    public function getRecent(int $limit = 10): Collection
    {
        return Activity::with(['causer', 'subject'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Search activity logs by description.
     */
    public function search(string $query, int $perPage = 20): LengthAwarePaginator
    {
        return Activity::with(['causer', 'subject'])
            ->where('description', 'like', "%{$query}%")
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Filter activity logs with multiple criteria.
     */
    public function filter(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Activity::with(['causer', 'subject']);

        // Filter by causer
        if (isset($filters['causer_id'])) {
            $query->where('causer_id', $filters['causer_id'])
                  ->where('causer_type', 'App\Models\User');
        }

        // Filter by subject type
        if (isset($filters['subject_type'])) {
            $query->where('subject_type', $filters['subject_type']);
        }

        // Filter by subject id
        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        // Filter by event
        if (isset($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        // Filter by date range
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('created_at', [
                $filters['start_date'],
                $filters['end_date']
            ]);
        }

        // Filter by log name
        if (isset($filters['log_name'])) {
            $query->where('log_name', $filters['log_name']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get activity statistics.
     */
    public function getStatistics(): array
    {
        $total = Activity::count();
        $today = Activity::whereDate('created_at', today())->count();
        $thisWeek = Activity::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisMonth = Activity::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $byEvent = Activity::selectRaw('event, COUNT(*) as count')
            ->groupBy('event')
            ->pluck('count', 'event')
            ->toArray();

        $byModel = Activity::selectRaw('subject_type, COUNT(*) as count')
            ->whereNotNull('subject_type')
            ->groupBy('subject_type')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'model' => class_basename($item->subject_type),
                    'count' => $item->count,
                ];
            })
            ->toArray();

        return [
            'total' => $total,
            'today' => $today,
            'this_week' => $thisWeek,
            'this_month' => $thisMonth,
            'by_event' => $byEvent,
            'by_model' => $byModel,
        ];
    }

    /**
     * Delete old activity logs.
     */
    public function deleteOlderThan(int $days): int
    {
        return Activity::where('created_at', '<', now()->subDays($days))->delete();
    }
}
