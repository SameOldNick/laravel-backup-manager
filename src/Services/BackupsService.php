<?php

namespace SameOldNick\BackupManager\Services;

use Illuminate\Support\Facades\URL;
use SameOldNick\BackupManager\Enums\BackupStatus;
use SameOldNick\BackupManager\Models\Backup;
use SameOldNick\BackupManager\Models\Collections\BackupCollection;

class BackupsService
{
    /**
     * Initializes BackupsService instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Filters backups based on status and search query.
     *
     * @param  BackupStatus|null  $status  Filter by backup status (e.g. "successful", "failed", "deleted", "file_not_found")
     * @param  string|null  $query  Search query to filter by backup name or description
     * @return BackupCollection Filtered collection of backups
     */
    public function getBackups(?BackupStatus $status = null, ?string $query = null): BackupCollection
    {
        $backupsQuery = Backup::query()->withTrashed()->with('file', function ($query) {
            $query->withTrashed();
        });

        if ($status !== null) {
            $backupsQuery->afterQuery(function ($backups) use ($status) {
                return $backups->filter(fn (Backup $backup) => $backup->status === $status);
            });
        }

        if ($query) {
            $backupsQuery->where(function ($q) use ($query) {
                $q->where('uuid', 'like', "%{$query}%")
                    ->orWhere('status', 'like', "%{$query}%")
                    ->orWhereHas('file', function ($q) use ($query) {
                        $q->where('path', 'like', "%{$query}%");
                    });
            });
        }

        return new BackupCollection($backupsQuery->latest()->get());
    }

    /**
     * Creates a temporary signed URL for downloading the backup file.
     *
     * @param  Backup  $backup  The backup instance for which to create the download link
     * @param  int  $expiresIn  The number of seconds until the link expires (default: 300 seconds)
     * @return string A temporary signed URL for downloading the backup file
     */
    public function createBackupDownloadLink(Backup $backup, int $expiresIn = 300): string
    {
        return $expiresIn > 0 ? URL::temporarySignedRoute(
            'backup.file',
            $expiresIn,
            ['file' => $backup->file]
        ) : URL::signedRoute('backup.file', ['file' => $backup->file]);
    }
}
