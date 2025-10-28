<?php

namespace App\Services;

use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class ActivityLogService
{
    protected ActivityLogRepositoryInterface $activityLogRepository;

    public function __construct(ActivityLogRepositoryInterface $activityLogRepository)
    {
        $this->activityLogRepository = $activityLogRepository;
    }

    /**
     * Get all activity logs with pagination.
     */
    public function getAllLogs(int $perPage = 20): LengthAwarePaginator
    {
        return $this->activityLogRepository->getAllPaginated($perPage);
    }

    /**
     * Get activity log detail by ID.
     */
    public function getLogDetail(int $id): ?Activity
    {
        return $this->activityLogRepository->getById($id);
    }

    /**
     * Get activity logs by user.
     */
    public function getLogsByUser(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->activityLogRepository->getByCauser($userId, $perPage);
    }

    /**
     * Get activity logs for a specific model instance.
     */
    public function getLogsByModel(string $modelType, int $modelId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->activityLogRepository->getBySubject($modelType, $modelId, $perPage);
    }

    /**
     * Get activity logs by model type.
     */
    public function getLogsByModelType(string $modelType, int $perPage = 20): LengthAwarePaginator
    {
        return $this->activityLogRepository->getByModelType($modelType, $perPage);
    }

    /**
     * Get activity logs by event.
     */
    public function getLogsByEvent(string $event, int $perPage = 20): LengthAwarePaginator
    {
        return $this->activityLogRepository->getByEvent($event, $perPage);
    }

    /**
     * Get activity logs within date range.
     */
    public function getLogsByDateRange(string $startDate, string $endDate, int $perPage = 20): LengthAwarePaginator
    {
        return $this->activityLogRepository->getByDateRange($startDate, $endDate, $perPage);
    }

    /**
     * Get recent activity logs.
     */
    public function getRecentLogs(int $limit = 10): Collection
    {
        return $this->activityLogRepository->getRecent($limit);
    }

    /**
     * Search activity logs.
     */
    public function searchLogs(string $query, int $perPage = 20): LengthAwarePaginator
    {
        return $this->activityLogRepository->search($query, $perPage);
    }

    /**
     * Filter activity logs with multiple criteria.
     */
    public function filterLogs(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->activityLogRepository->filter($filters, $perPage);
    }

    /**
     * Get activity statistics.
     */
    public function getStatistics(): array
    {
        return $this->activityLogRepository->getStatistics();
    }

    /**
     * Log custom activity.
     */
    public function logCustomActivity(string $description, ?object $subject = null, array $properties = []): Activity
    {
        $activity = activity()
            ->causedBy(Auth::user())
            ->withProperties($properties);

        if ($subject) {
            $activity->performedOn($subject);
        }

        return $activity->log($description);
    }

    /**
     * Clean up old activity logs.
     */
    public function cleanupOldLogs(int $days = 90): int
    {
        return $this->activityLogRepository->deleteOlderThan($days);
    }

    /**
     * Format activity log for display.
     */
    public function formatLogForDisplay(Activity $log): array
    {
        return [
            'id' => $log->id,
            'description' => $log->description,
            'event' => $log->event,
            'subject_type' => $log->subject_type ? class_basename($log->subject_type) : null,
            'subject_id' => $log->subject_id,
            'causer' => $log->causer ? [
                'id' => $log->causer->id,
                'name' => $log->causer->name,
                'email' => $log->causer->email,
            ] : null,
            'properties' => $log->properties,
            'created_at' => $log->created_at->format('Y-m-d H:i:s'),
            'created_at_human' => $log->created_at->diffForHumans(),
        ];
    }

    /**
     * Get activity logs for dashboard (recent + summary).
     */
    public function getDashboardData(): array
    {
        $recent = $this->getRecentLogs(10)->map(function ($log) {
            return $this->formatLogForDisplay($log);
        });

        $statistics = $this->getStatistics();

        return [
            'recent_activities' => $recent,
            'statistics' => $statistics,
        ];
    }

    /**
     * Get user activity summary.
     */
    public function getUserActivitySummary(int $userId): array
    {
        $total = Activity::where('causer_id', $userId)
            ->where('causer_type', 'App\Models\User')
            ->count();

        $byEvent = Activity::where('causer_id', $userId)
            ->where('causer_type', 'App\Models\User')
            ->selectRaw('event, COUNT(*) as count')
            ->groupBy('event')
            ->pluck('count', 'event')
            ->toArray();

        $recent = $this->getLogsByUser($userId, 5);

        return [
            'total_activities' => $total,
            'by_event' => $byEvent,
            'recent_activities' => $recent->items(),
        ];
    }
}
