<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Run a full backup (database + files).
     *
     * @return JsonResponse
     */
    public function runFull(): JsonResponse
    {
        $result = $this->backupService->runFullBackup();

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Run database-only backup.
     *
     * @return JsonResponse
     */
    public function runDatabase(): JsonResponse
    {
        $result = $this->backupService->runDatabaseBackup();

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Run files-only backup.
     *
     * @return JsonResponse
     */
    public function runFiles(): JsonResponse
    {
        $result = $this->backupService->runFilesBackup();

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Get list of all backups.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $backups = $this->backupService->listBackups();

        return response()->json([
            'success' => true,
            'message' => 'Backups retrieved successfully.',
            'data' => $backups,
            'count' => $backups->count(),
        ]);
    }

    /**
     * Get backup statistics.
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        $statistics = $this->backupService->getStatistics();

        return response()->json([
            'success' => true,
            'message' => 'Backup statistics retrieved successfully.',
            'data' => $statistics,
        ]);
    }

    /**
     * Get last backup information.
     *
     * @return JsonResponse
     */
    public function latest(): JsonResponse
    {
        $lastBackup = $this->backupService->getLastBackup();

        if (!$lastBackup) {
            return response()->json([
                'success' => false,
                'message' => 'No backups found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Latest backup retrieved successfully.',
            'data' => $lastBackup,
        ]);
    }

    /**
     * Download a backup file.
     *
     * @param string $filename
     * @return BinaryFileResponse|JsonResponse
     */
    public function download(string $filename)
    {
        if (!$this->backupService->backupExists($filename)) {
            return response()->json([
                'success' => false,
                'message' => 'Backup file not found.',
            ], 404);
        }

        $path = $this->backupService->getDownloadPath($filename);

        if (!$path) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to generate download path.',
            ], 500);
        }

        return response()->download($path, $filename);
    }

    /**
     * Delete a specific backup.
     *
     * @param string $filename
     * @return JsonResponse
     */
    public function destroy(string $filename): JsonResponse
    {
        $result = $this->backupService->deleteBackup($filename);

        return response()->json($result, $result['success'] ? 200 : 404);
    }

    /**
     * Clean up old backups.
     *
     * @return JsonResponse
     */
    public function cleanup(): JsonResponse
    {
        $result = $this->backupService->cleanupOldBackups();

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Monitor backup health.
     *
     * @return JsonResponse
     */
    public function monitor(): JsonResponse
    {
        $result = $this->backupService->monitorBackups();

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Check backup health status.
     *
     * @return JsonResponse
     */
    public function health(): JsonResponse
    {
        $isHealthy = $this->backupService->isHealthy();
        $lastBackup = $this->backupService->getLastBackup();

        return response()->json([
            'success' => true,
            'healthy' => $isHealthy,
            'message' => $isHealthy 
                ? 'Backups are healthy.' 
                : 'Warning: No recent backups found (last 24 hours).',
            'last_backup' => $lastBackup,
        ]);
    }

    /**
     * Get backup schedule information.
     *
     * @return JsonResponse
     */
    public function schedule(): JsonResponse
    {
        $scheduleInfo = $this->backupService->getScheduleInfo();

        return response()->json([
            'success' => true,
            'message' => 'Backup schedule retrieved successfully.',
            'data' => $scheduleInfo,
        ]);
    }
}
