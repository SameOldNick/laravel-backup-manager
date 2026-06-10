<?php

namespace SameOldNick\BackupManager\Services;

use SameOldNick\BackupManager\Broadcasting\Access\ChannelAccessManager;
use SameOldNick\BackupManager\Broadcasting\Access\ChannelLease;
use SameOldNick\BackupManager\Enums\BackupTypes;
use SameOldNick\BackupManager\Jobs\Notifiable\BackupJob;
use SameOldNick\BackupManager\Models\BackupRun;

class PerformBackupService
{
    /**
     * Initializes PerformBackupService instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Starts a backup process by dispatching a BackupJob and creating a channel lease for real-time updates.
     *
     * @param  string  $uuid  The UUID for the backup process (used for channel ID generation)
     * @param  object  $user  The user initiating the backup (used for channel lease)
     * @return ChannelLease A lease for the backup channel to receive real-time updates
     *
     * @throws \InvalidArgumentException If an invalid backup type is provided
     */
    public function openBackupChannel(string $uuid, object $user): ChannelLease
    {
        $channel = $this->createChannelId($uuid);

        $lease = $this->openBackupChannelLease($channel, $user);

        return $lease;
    }

    /**
     * Dispatches a backup job to perform the backup process and creates a BackupRun record.
     *
     * @param  ChannelLease  $lease  The channel lease for real-time updates during the backup process
     * @param  BackupTypes  $type  The type of backup to perform (e.g. full, incremental)
     * @param  object  $user  The user initiating the backup (used for job dispatching)
     * @return BackupRun The created BackupRun record representing the backup process
     *
     * @throws \InvalidArgumentException If an invalid backup type is provided
     */
    public function dispatchBackupJob(ChannelLease $lease, BackupTypes $type, object $user): BackupRun
    {
        /** @var BackupRun $backupRun */
        $backupRun = BackupRun::create([
            'type' => $type,
        ]);

        dispatch(new BackupJob($backupRun->getKey(), $lease->channelId, $user, $type));

        return $backupRun;
    }

    /**
     * Dispatches a backup job only once for a given UUID.
     *
     * Returns null when the run was already created for this UUID.
     */
    public function dispatchBackupJobOnce(ChannelLease $lease, BackupTypes $type, object $user, string $uuid): ?BackupRun
    {
        /**
         * A insertOrIgnore is used to ensure that only one BackupRun is created for a given UUID, even if this method is called multiple times concurrently.
         * The channel lease is used to ensure that the backup job is only dispatched once for a given UUID, even if this method is called multiple times concurrently across different instances of the application (e.g. multiple web servers).
         */

        /** @var int $inserted */
        $inserted = BackupRun::query()->insertOrIgnore([
            'id' => $uuid,
            'type' => $type->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($inserted === 0) {
            return null;
        }

        /** @var BackupRun $backupRun */
        $backupRun = BackupRun::query()->findOrFail($uuid);

        dispatch(new BackupJob($backupRun->getKey(), $lease->channelId, $user, $type));

        return $backupRun;
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
