<?php

namespace SameOldNick\BackupManager\Concerns;

use SameOldNick\BackupManager\Broadcasting\Access\ChannelAccessManager;
use SameOldNick\BackupManager\Broadcasting\Access\ChannelLease;

trait AcquiresChannelLease
{
    /**
     * Opens a channel lease.
     *
     * @param  string  $channel  The channel ID to open (e.g. "backups.{uuid}")
     * @param  object  $user  The user for whom to open the channel lease
     * @return ChannelLease A lease for the opened channel
     */
    public function openChannelLease(string $channel, object $user): ChannelLease
    {
        return app(ChannelAccessManager::class)->open(
            channelId: $channel,
            notifiable: $user,
            expiresAt: now()->addMinutes($this->getChannelLeaseExpirationMinutes()),
        );
    }

    /**
     * Retrieves the channel lease for a given channel ID.
     *
     * @param  string  $channel  The channel ID to retrieve the lease for (e.g. "backups.{uuid}")
     * @return ChannelLease|null The channel lease if found and valid, or null if not found or expired
     */
    public function getChannelLease(string $channel): ?ChannelLease
    {
        return app(ChannelAccessManager::class)->get($channel);
    }

    /**
     * Gets the channel lease expiration time in minutes from the configuration.
     */
    abstract protected function getChannelLeaseExpirationMinutes(): int;
}
