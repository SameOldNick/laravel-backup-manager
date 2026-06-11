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
     * @param  ?\DateTimeInterface  $expiresAt  The date and time when the lease expires
     * @return ChannelLease A lease for the opened channel
     */
    public function openChannelLease(string $channel, object $user, ?\DateTimeInterface $expiresAt): ChannelLease
    {
        return app(ChannelAccessManager::class)->open(
            channelId: $channel,
            notifiable: $user,
            expiresAt: $expiresAt,
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
}
