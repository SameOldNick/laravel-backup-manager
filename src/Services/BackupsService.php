<?php

namespace SameOldNick\BackupManager\Services;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use SameOldNick\BackupManager\Broadcasting\Access\ChannelAccessManager;
use SameOldNick\BackupManager\Broadcasting\Access\ChannelLease;
use SameOldNick\BackupManager\Enums\BackupStatus;
use SameOldNick\BackupManager\Jobs\Notifiable\BackupJob;
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

    /**
     * Starts a backup process by dispatching a BackupJob and creating a channel lease for real-time updates.
     *
     * @param  string  $type  The type of backup to perform (e.g. "full", "database", "files")
     * @param  object  $user  The user initiating the backup (used for channel lease)
     * @param  string|null  $uuid  Optional UUID for the backup process (if not provided, a new UUID will be generated)
     * @return ChannelLease A lease for the backup channel to receive real-time updates
     *
     * @throws \InvalidArgumentException If an invalid backup type is provided
     */
    public function startBackup(string $type, object $user, ?string $uuid = null): ChannelLease
    {
        if (! in_array($type, [BackupJob::BACKUP_ONLY_DATABASES, BackupJob::BACKUP_ONLY_FILES, BackupJob::BACKUP_FULL])) {
            throw new \InvalidArgumentException("Invalid backup type: {$type}");
        }

        $channel = $this->createChannelId($uuid ?? Str::uuid());

        $lease = $this->openBackupChannelLease($channel, $user);

        dispatch(new BackupJob($channel, $user, $type));

        return $lease;
    }

    /**
     * Creates a unique channel ID for the backup process based on the given UUID.
     *
     * @param  string  $uuid  The UUID to include in the channel ID
     * @return string A unique channel ID for the backup process (e.g. "backups.{uuid}")
     */
    public function createChannelId(string $uuid): string
    {
        return app(ChannelAccessManager::class)->createChannelId('backups', $uuid);
    }

    /**
     * Opens a channel lease for the backup process to allow real-time updates.
     *
     * @param  string  $channel  The channel ID to open (e.g. "backups.{uuid}")
     * @param  object  $user  The user for whom to open the channel lease
     * @return ChannelLease A lease for the opened channel
     */
    public function openBackupChannelLease(string $channel, object $user): ChannelLease
    {
        return app(ChannelAccessManager::class)->open(
            channelId: $channel,
            notifiable: $user,
            expiresAt: now()->addHours(3),
        );
    }

    /**
     * Retrieves the channel lease for a given channel ID.
     *
     * @param  string  $channel  The channel ID to retrieve the lease for (e.g. "backups.{uuid}")
     * @return ChannelLease|null The channel lease if found and valid, or null if not found or expired
     */
    public function getBackupChannelLease(string $channel): ?ChannelLease
    {
        return app(ChannelAccessManager::class)->get($channel);
    }
}
