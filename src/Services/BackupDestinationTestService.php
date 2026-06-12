<?php

namespace SameOldNick\BackupManager\Services;

use SameOldNick\BackupManager\Broadcasting\Access\ChannelLease;
use SameOldNick\BackupManager\Concerns;
use SameOldNick\BackupManager\Jobs\Notifiable\FilesystemConfigurationTestJob;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;

class BackupDestinationTestService
{
    use Concerns\AcquiresChannelLease;
    use Concerns\GeneratesChannelId;

    /**
     * Initializes BackupDestinationTestService instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Opens a channel lease for a backup destination test.
     *
     * @param  string  $uuid  The UUID for the backup destination test (used for channel ID generation)
     * @param  object  $user  The user initiating the test (used for channel lease)
     * @return ChannelLease A lease for the backup destination test channel to receive real-time updates
     */
    public function openBackupDestinationTestChannel(string $uuid, object $user): ChannelLease
    {
        $channel = $this->createChannelId($uuid);

        $lease = $this->openChannelLease($channel, $user, now()->addHours(3));

        return $lease;
    }

    /**
     * Starts a backup destination test by dispatching a FilesystemConfigurationTestJob.
     *
     * @param  FilesystemConfiguration  $destination  The filesystem configuration to test
     * @param  string  $uuid  The UUID for the backup destination test (used for channel ID generation)
     * @param  object  $user  The user initiating the test (used for job dispatching)
     *
     * @throws \RuntimeException If the channel lease is not found or if the user is unauthorized to use the lease
     */
    public function startBackupDestinationTest(FilesystemConfiguration $destination, string $uuid, object $user): void
    {
        $lease = $this->getBackupDestinationTestChannelLease($uuid);

        if ($lease === null) {
            throw new \RuntimeException('Backup destination test channel lease not found for UUID: '.$uuid);
        }

        if ($lease->notifiableClass !== $user::class || $lease->notifiableKey !== (string) $user->getAuthIdentifier()) {
            throw new \RuntimeException('Unauthorized access to backup destination test channel lease for UUID: '.$uuid);
        }

        dispatch(new FilesystemConfigurationTestJob($this->createChannelId($uuid), $user, $destination->driver_name));
    }

    /**
     * Retrieves the channel lease for a given backup destination test UUID.
     *
     * @param  string  $uuid  The UUID for the backup destination test (used for channel ID generation)
     * @return ChannelLease|null The channel lease if found and valid, or null if not found or expired
     */
    public function getBackupDestinationTestChannelLease(string $uuid): ?ChannelLease
    {
        return $this->getChannelLease($this->createChannelId($uuid));
    }

    /**
     * {@inheritDoc}
     */
    protected function getChannelIdPrefix(): string
    {
        return 'test-destination';
    }
}
