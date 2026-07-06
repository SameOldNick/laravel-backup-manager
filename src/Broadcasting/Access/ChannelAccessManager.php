<?php

namespace SameOldNick\BackupManager\Broadcasting\Access;

use Carbon\CarbonInterface;
use DateTimeInterface;
use Ramsey\Uuid\UuidInterface;
use SameOldNick\BackupManager\Contracts\ChannelAccessStore;

class ChannelAccessManager
{
    /**
     * Initializes ChannelAccessManager instance.
     *
     * @param  ChannelAccessStore  $store  Store to manage channel access data
     */
    public function __construct(
        public readonly ChannelAccessStore $store,
    ) {
        //
    }

    /**
     * Get the channel data.
     *
     * @param  string  $channelId  Channel ID (e.g. "jobs.{uuid}")
     * @return ChannelLease|null Channel lease or null if not found
     */
    public function get(string $channelId): ?ChannelLease
    {
        $data = $this->store->get($channelId);

        if (! is_array($data)) {
            return null;
        }

        return new ChannelLease(
            manager: $this,
            channelId: $channelId,
            notifiableClass: $data['notifiable_class'] ?? null,
            notifiableKey: $data['notifiable_key'] ?? null,
            expiresAt: $data['expires_at'] ?? null,
        );
    }

    /**
     * Opens a private channel for the given notifiable instance.
     *
     * @param  string  $channelId  Channel ID (e.g. "jobs.{uuid}")
     * @param  object  $notifiable  The notifiable instance
     * @param  DateTimeInterface  $expiresAt  When channel expires
     * @return ChannelLease The created channel lease
     */
    public function open(
        string $channelId,
        object $notifiable,
        ?CarbonInterface $expiresAt = null,
    ): ChannelLease {
        $data = $this->store->open($channelId, $notifiable, $expiresAt);

        return new ChannelLease(
            manager: $this,
            channelId: $channelId,
            notifiableClass: $data['notifiable_class'],
            notifiableKey: $data['notifiable_key'],
            expiresAt: $expiresAt
        );
    }

    /**
     * Closes the channel.
     *
     * @param  string  $channelId  Channel ID (e.g. "jobs.{uuid}")
     */
    public function close(string $channelId): void
    {
        $this->store->close($channelId);
    }

    /**
     * Creates a channel ID by combining the channel name and a UUID.
     *
     * @param  string  $channelName  The base name of the channel (e.g. "jobs")
     * @param  UuidInterface|string  $uuid  A UUID to ensure uniqueness
     * @return string The generated channel ID (e.g. "jobs.{uuid}")
     */
    public function createChannelId(string $channelName, UuidInterface|string $uuid): string
    {
        return "{$channelName}.{$uuid}";
    }

    /**
     * Parses the channel ID to extract the channel name and UUID.
     *
     * @param  string  $channelId  Channel ID (e.g. "jobs.{uuid}")
     * @return array{channel:string, uuid:string}|null Parsed channel name and UUID, or null if format is invalid
     */
    public function getChannelNameAndUuid(string $channelId): ?array
    {
        if (! str_contains($channelId, '.')) {
            return null;
        }

        [$channelName, $uuid] = explode('.', $channelId, 2);

        return [
            'channel' => $channelName,
            'uuid' => $uuid,
        ];
    }
}
