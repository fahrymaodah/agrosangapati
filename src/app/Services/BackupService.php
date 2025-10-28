<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Spatie\Backup\Tasks\Backup\BackupJob;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Carbon\Carbon;

class BackupService
{
    protected string $backupDisk;
    protected string $backupName;

    public function __construct()
    {
        $this->backupDisk = config('backup.backup.destination.disks')[0] ?? 'local';
        $this->backupName = config('backup.backup.name');
    }

    /**
     * Run a full backup (database + files).
     */
    public function runFullBackup(): array
    {
        try {
            Artisan::call('backup:run');
            $output = Artisan::output();

            return [
                'success' => true,
                'message' => 'Full backup completed successfully.',
                'output' => $output,
                'timestamp' => now()->toDateTimeString(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Run database-only backup.
     */
    public function runDatabaseBackup(): array
    {
        try {
            Artisan::call('backup:run', ['--only-db' => true]);
            $output = Artisan::output();

            return [
                'success' => true,
                'message' => 'Database backup completed successfully.',
                'output' => $output,
                'timestamp' => now()->toDateTimeString(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Database backup failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Run files-only backup.
     */
    public function runFilesBackup(): array
    {
        try {
            Artisan::call('backup:run', ['--only-files' => true]);
            $output = Artisan::output();

            return [
                'success' => true,
                'message' => 'Files backup completed successfully.',
                'output' => $output,
                'timestamp' => now()->toDateTimeString(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Files backup failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get list of all backups.
     */
    public function listBackups(): Collection
    {
        $config = \Spatie\Backup\Config\Config::fromArray(config('backup'));
        $backupDestinations = BackupDestinationFactory::createFromArray($config);
        
        $allBackups = collect();
        
        foreach ($backupDestinations as $backupDestination) {
            $backups = $backupDestination->backups()->map(function ($backup) {
                return [
                    'name' => basename($backup->path()),
                    'path' => $backup->path(),
                    'date' => $backup->date()->format('Y-m-d H:i:s'),
                    'date_human' => $backup->date()->diffForHumans(),
                    'size' => $this->formatBytes($backup->sizeInBytes()),
                    'size_bytes' => $backup->sizeInBytes(),
                ];
            });
            
            $allBackups = $allBackups->merge($backups);
        }
        
        return $allBackups->sortByDesc('date');
    }

    /**
     * Get backup statistics.
     */
    public function getStatistics(): array
    {
        $backups = $this->listBackups();
        $totalSize = $backups->sum('size_bytes');
        $newestBackup = $backups->first();
        $oldestBackup = $backups->last();

        return [
            'total_backups' => $backups->count(),
            'total_size' => $this->formatBytes($totalSize),
            'total_size_bytes' => $totalSize,
            'newest_backup' => $newestBackup ? [
                'date' => $newestBackup['date'],
                'date_human' => $newestBackup['date_human'],
                'size' => $newestBackup['size'],
            ] : null,
            'oldest_backup' => $oldestBackup ? [
                'date' => $oldestBackup['date'],
                'date_human' => $oldestBackup['date_human'],
                'size' => $oldestBackup['size'],
            ] : null,
            'disk' => $this->backupDisk,
            'backup_name' => $this->backupName,
        ];
    }

    /**
     * Delete a specific backup file.
     */
    public function deleteBackup(string $filename): array
    {
        try {
            $backupPath = $this->backupName . '/' . $filename;

            if (!Storage::disk($this->backupDisk)->exists($backupPath)) {
                return [
                    'success' => false,
                    'message' => 'Backup file not found.',
                ];
            }

            Storage::disk($this->backupDisk)->delete($backupPath);

            return [
                'success' => true,
                'message' => 'Backup deleted successfully.',
                'filename' => $filename,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete backup: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Clean up old backups.
     */
    public function cleanupOldBackups(): array
    {
        try {
            Artisan::call('backup:clean');
            $output = Artisan::output();

            return [
                'success' => true,
                'message' => 'Old backups cleaned up successfully.',
                'output' => $output,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Cleanup failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Monitor backup health.
     */
    public function monitorBackups(): array
    {
        try {
            Artisan::call('backup:monitor');
            $output = Artisan::output();

            return [
                'success' => true,
                'message' => 'Backup monitoring completed.',
                'output' => $output,
                'healthy' => !str_contains($output, 'unhealthy'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Monitoring failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'healthy' => false,
            ];
        }
    }

    /**
     * Get backup file download path.
     */
    public function getDownloadPath(string $filename): ?string
    {
        $backupPath = $this->backupName . '/' . $filename;

        if (!Storage::disk($this->backupDisk)->exists($backupPath)) {
            return null;
        }

        return Storage::disk($this->backupDisk)->path($backupPath);
    }

    /**
     * Get backup file URL for download.
     */
    public function getDownloadUrl(string $filename): ?string
    {
        $backupPath = $this->backupName . '/' . $filename;

        if (!Storage::disk($this->backupDisk)->exists($backupPath)) {
            return null;
        }

        // For local disk, create temporary URL
        if ($this->backupDisk === 'local') {
            return Storage::disk($this->backupDisk)->path($backupPath);
        }

        // For cloud storage (S3, etc), generate temporary URL
        return Storage::disk($this->backupDisk)->temporaryUrl(
            $backupPath,
            now()->addHours(1)
        );
    }

    /**
     * Check if backup file exists.
     */
    public function backupExists(string $filename): bool
    {
        $backupPath = $this->backupName . '/' . $filename;
        return Storage::disk($this->backupDisk)->exists($backupPath);
    }

    /**
     * Get the backup directory path.
     */
    protected function getBackupPath(): string
    {
        return $this->backupName;
    }

    /**
     * Format bytes to human-readable size.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Get last backup information.
     */
    public function getLastBackup(): ?array
    {
        $backups = $this->listBackups();
        return $backups->first();
    }

    /**
     * Check if backups are healthy (last backup within 24 hours).
     */
    public function isHealthy(): bool
    {
        $lastBackup = $this->getLastBackup();

        if (!$lastBackup) {
            return false;
        }

        $lastBackupDate = Carbon::parse($lastBackup['date']);
        return $lastBackupDate->diffInHours(now()) < 24;
    }

    /**
     * Get backup schedule info.
     */
    public function getScheduleInfo(): array
    {
        return [
            'enabled' => true,
            'frequency' => 'daily',
            'time' => '02:00',
            'next_run' => $this->getNextScheduledRun(),
            'timezone' => config('app.timezone'),
        ];
    }

    /**
     * Calculate next scheduled backup run.
     */
    protected function getNextScheduledRun(): string
    {
        $now = now();
        $scheduledTime = $now->copy()->setTime(2, 0, 0);

        if ($now->greaterThan($scheduledTime)) {
            $scheduledTime->addDay();
        }

        return $scheduledTime->format('Y-m-d H:i:s');
    }
}
