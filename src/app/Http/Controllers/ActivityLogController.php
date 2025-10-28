<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of activity logs.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $logs = $this->activityLogService->getAllLogs($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Activity logs retrieved successfully.',
            'data' => $logs,
        ]);
    }

    /**
     * Display the specified activity log.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $log = $this->activityLogService->getLogDetail($id);

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Activity log not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Activity log retrieved successfully.',
            'data' => $this->activityLogService->formatLogForDisplay($log),
        ]);
    }

    /**
     * Get activity logs by user.
     *
     * @param int $userId
     * @param Request $request
     * @return JsonResponse
     */
    public function byUser(int $userId, Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $logs = $this->activityLogService->getLogsByUser($userId, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'User activity logs retrieved successfully.',
            'data' => $logs,
        ]);
    }

    /**
     * Get activity logs by model type and ID.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function byModel(Request $request): JsonResponse
    {
        $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        $perPage = $request->input('per_page', 20);
        $modelType = 'App\Models\\' . $request->input('model_type');

        $logs = $this->activityLogService->getLogsByModel($modelType, $request->input('model_id'), $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Model activity logs retrieved successfully.',
            'data' => $logs,
        ]);
    }

    /**
     * Get activity logs by model type.
     *
     * @param string $modelType
     * @param Request $request
     * @return JsonResponse
     */
    public function byModelType(string $modelType, Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $fullModelType = 'App\Models\\' . $modelType;

        $logs = $this->activityLogService->getLogsByModelType($fullModelType, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Activity logs retrieved successfully.',
            'data' => $logs,
        ]);
    }

    /**
     * Get activity logs by event.
     *
     * @param string $event
     * @param Request $request
     * @return JsonResponse
     */
    public function byEvent(string $event, Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $logs = $this->activityLogService->getLogsByEvent($event, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Activity logs retrieved successfully.',
            'data' => $logs,
        ]);
    }

    /**
     * Get activity logs by date range.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function byDateRange(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $perPage = $request->input('per_page', 20);
        $logs = $this->activityLogService->getLogsByDateRange(
            $request->input('start_date'),
            $request->input('end_date'),
            $perPage
        );

        return response()->json([
            'success' => true,
            'message' => 'Activity logs retrieved successfully.',
            'data' => $logs,
        ]);
    }

    /**
     * Get recent activity logs.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function recent(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $logs = $this->activityLogService->getRecentLogs($limit);

        $formattedLogs = $logs->map(function ($log) {
            return $this->activityLogService->formatLogForDisplay($log);
        });

        return response()->json([
            'success' => true,
            'message' => 'Recent activity logs retrieved successfully.',
            'data' => $formattedLogs,
        ]);
    }

    /**
     * Search activity logs.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $perPage = $request->input('per_page', 20);
        $logs = $this->activityLogService->searchLogs($request->input('query'), $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Activity logs search results.',
            'data' => $logs,
        ]);
    }

    /**
     * Filter activity logs.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function filter(Request $request): JsonResponse
    {
        $filters = $request->only([
            'causer_id',
            'subject_type',
            'subject_id',
            'event',
            'start_date',
            'end_date',
            'log_name'
        ]);

        // Convert subject_type to full class name if provided
        if (isset($filters['subject_type'])) {
            $filters['subject_type'] = 'App\Models\\' . $filters['subject_type'];
        }

        $perPage = $request->input('per_page', 20);
        $logs = $this->activityLogService->filterLogs($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Filtered activity logs retrieved successfully.',
            'data' => $logs,
        ]);
    }

    /**
     * Get activity statistics.
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        $statistics = $this->activityLogService->getStatistics();

        return response()->json([
            'success' => true,
            'message' => 'Activity statistics retrieved successfully.',
            'data' => $statistics,
        ]);
    }

    /**
     * Get dashboard data.
     *
     * @return JsonResponse
     */
    public function dashboard(): JsonResponse
    {
        $data = $this->activityLogService->getDashboardData();

        return response()->json([
            'success' => true,
            'message' => 'Activity dashboard data retrieved successfully.',
            'data' => $data,
        ]);
    }

    /**
     * Get user activity summary.
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function userSummary(int $userId): JsonResponse
    {
        $summary = $this->activityLogService->getUserActivitySummary($userId);

        return response()->json([
            'success' => true,
            'message' => 'User activity summary retrieved successfully.',
            'data' => $summary,
        ]);
    }

    /**
     * Log custom activity.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logCustom(Request $request): JsonResponse
    {
        $request->validate([
            'description' => 'required|string',
            'properties' => 'sometimes|array',
        ]);

        $log = $this->activityLogService->logCustomActivity(
            $request->input('description'),
            null,
            $request->input('properties', [])
        );

        return response()->json([
            'success' => true,
            'message' => 'Custom activity logged successfully.',
            'data' => $this->activityLogService->formatLogForDisplay($log),
        ], 201);
    }
}
