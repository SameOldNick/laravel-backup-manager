<?php

namespace SameOldNick\BackupManager\Contracts;

use DateTimeInterface;

interface ChannelAccessStore
{
    /**
     * Opens private channel
     *
     * @param  string  $channelId  Channel ID (e.g. "jobs.{uuid}")
     * @param  object  $notifiable  The notifiable instance
     * @param  DateTimeInterface  $expiresAt  When channel expires
     */
    public function open(string $channelId, object $notifiable, DateTimeInterface $expiresAt): array;

    /**
     * Closes the channel.
     *
     * @param  string  $channelId  Channel ID (e.g. "jobs.{uuid}")
     */
    public function close(string $channelId): void;

    /**
     * Get the channel data.
     *
     * @param  string  $channelId  Channel ID (e.g. "jobs.{uuid}")
     * @return array{channel:string, notifiable_class:string, notifiable_key:string, expires_at:DateTimeInterface}|null Channel data or null if not found
     */
    public function get(string $channelId): ?array;
}
