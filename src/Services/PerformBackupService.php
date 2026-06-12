<?php

namespace SameOldNick\BackupManager\Services;

use SameOldNick\BackupManager\Broadcasting\Access\ChannelLease;
use SameOldNick\BackupManager\Concerns;
use SameOldNick\BackupManager\Enums\BackupTypes;
use SameOldNick\BackupManager\Jobs\Notifiable\BackupJob;
use SameOldNick\BackupManager\Models\BackupRun;

class PerformBackupService
{
    use Concerns\AcquiresChannelLease;
    use Concerns\GeneratesChannelId;

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

        $lease = $this->openChannelLease($channel, $user, now()->addHours(3));

        return $lease;
    }

    /**
     * Retrieves the channel lease for a given UUID.
     *
     * @param  string  $uuid  The UUID for the backup process (used for channel ID generation)
     * @return ChannelLease|null The channel lease if found and valid, or null if not found or expired
     */
    public function getBackupChannelLease(string $uuid): ?ChannelLease
    {
        return $this->getChannelLease($this->createChannelId($uuid));
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
    public function dispatchBackupJobOnce(BackupTypes $type, object $user, string $uuid): BackupRun
    {
        $lease = $this->getBackupChannelLease($uuid);

        if ($lease === null) {
            throw new \RuntimeException('Backup channel lease not found for UUID: '.$uuid);
        }

        if ($lease->notifiableClass !== $user::class || $lease->notifiableKey !== (string) $user->getAuthIdentifier()) {
            throw new \RuntimeException('Unauthorized access to backup channel lease for UUID: '.$uuid);
        }

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
            // No rows were inserted, which means a BackupRun with this UUID already exists
            throw new \RuntimeException('Backup run already exists for UUID: '.$uuid);
        }

        /** @var BackupRun $backupRun */
        $backupRun = BackupRun::query()->findOrFail($uuid);

        dispatch(new BackupJob($backupRun->getKey(), $lease->channelId, $user, $type));

        return $backupRun;
    }

    /**
     * {@inheritDoc}
     */
    protected function getChannelIdPrefix(): string
    {
        return 'backups';
    }
}
