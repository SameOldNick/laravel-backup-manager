<?php

namespace SameOldNick\BackupManager\Services;

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
     * @param  string|null  $status  Filter by backup status (e.g. "successful", "failed", "deleted", "file_not_found")
     * @param  string|null  $query  Search query to filter by backup name or description
     * @return BackupCollection Filtered collection of backups
     */
    public function getBackups(?string $status = null, ?string $query = null): BackupCollection
    {
        $backupsQuery = Backup::query()->with('file');

        if ($status && $status !== 'all') {
            $backupsQuery->where('status', $status);
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
}
