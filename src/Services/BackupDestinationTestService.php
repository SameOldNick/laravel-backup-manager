<?php

namespace SameOldNick\BackupManager\Services;

use SameOldNick\BackupManager\Broadcasting\Access\ChannelLease;
use SameOldNick\BackupManager\Enums\RunStatus;
use SameOldNick\BackupManager\Jobs\Notifiable\FilesystemConfigurationTestJob;
use SameOldNick\BackupManager\Models\BackupDestinationTestRun;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;

class BackupDestinationTestService extends AbstractChannelLeaseService
{
    /**
     * Opens a channel lease for a backup destination test.
     *
     * @param  string  $uuid  The UUID for the backup destination test (used for channel ID generation)
     * @param  object  $user  The user initiating the test (used for channel lease)
     * @return ChannelLease A lease for the backup destination test channel to receive real-time updates
     */
    public function openBackupDestinationTestChannel(string $uuid, object $user): ChannelLease
    {
        return $this->openChannelLeaseForUuid($uuid, $user);
    }

    /**
     * Dispatches test job
     */
    public function dispatchTestJob(BackupDestinationTestRun $testRun, ChannelLease $lease, object $user): void
    {
        dispatch(new FilesystemConfigurationTestJob($testRun->getKey(), $lease->channelId, $user));
    }

    /**
     * Dispatches a backup destination test job if one has not already been dispatched for the given UUID.
     *
     * @param  FilesystemConfiguration  $destination  The filesystem configuration to test
     * @param  object  $user  The user initiating the test (used for job dispatching)
     * @param  string  $uuid  The UUID for the backup destination test (used for channel ID generation and test run tracking)
     * @return BackupDestinationTestRun The created BackupDestinationTestRun record representing the test process
     *
     * @throws \RuntimeException If a test run already exists for the given UUID or if the channel lease is not found or unauthorized
     */
    public function dispatchTestJobOnce(FilesystemConfiguration $destination, object $user, string $uuid): BackupDestinationTestRun
    {
        $lease = $this->requireChannelLeaseForUuid($uuid, $user);

        /** @var int $inserted */
        $inserted = BackupDestinationTestRun::query()->insertOrIgnore([
            'id' => $uuid,
            'filesystem_configuration_id' => $destination->id,
            'status' => RunStatus::Pending->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($inserted === 0) {
            // No rows were inserted, which means a BackupDestinationTestRun with this UUID already exists
            throw new \RuntimeException('Test run already exists for UUID: '.$uuid);
        }

        /** @var BackupDestinationTestRun $testRun */
        $testRun = BackupDestinationTestRun::query()->findOrFail($uuid);

        $this->dispatchTestJob($testRun, $lease, $user);

        return $testRun;
    }

    /**
     * Retrieves the channel lease for a given backup destination test UUID.
     *
     * @param  string  $uuid  The UUID for the backup destination test (used for channel ID generation)
     * @return ChannelLease|null The channel lease if found and valid, or null if not found or expired
     */
    public function getBackupDestinationTestChannelLease(string $uuid): ?ChannelLease
    {
        return $this->getChannelLeaseForUuid($uuid);
    }

    /**
     * {@inheritDoc}
     */
    protected function getChannelIdPrefix(): string
    {
        return config('backup-manager.channel_leases.test_backup_destination.prefix', 'test-destination');
    }

    /**
     * {@inheritDoc}
     */
    protected function getChannelLeaseExpirationMinutes(): int
    {
        return config('backup-manager.channel_leases.test_backup_destination.ttl', 180);
    }

    /**
     * Gets the channel lease not found exception for destination tests.
     */
    protected function makeChannelLeaseNotFoundException(string $uuid): \Throwable
    {
        return new \RuntimeException('Backup destination test channel lease not found for UUID: '.$uuid);
    }

    /**
     * Gets the channel lease unauthorized exception for destination tests.
     */
    protected function makeChannelLeaseUnauthorizedException(string $uuid): \Throwable
    {
        return new \RuntimeException('Unauthorized access to backup destination test channel lease for UUID: '.$uuid);
    }
}
