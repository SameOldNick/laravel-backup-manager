<?php

namespace SameOldNick\BackupManager\Broadcasting\Access\Stores;

use DateTimeInterface;
use Illuminate\Cache\Repository;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;
use SameOldNick\BackupManager\Contracts\ChannelAccessStore;

class CacheStore implements ChannelAccessStore
{
    /**
     * Initializes CacheStore instance.
     *
     * @param  Repository  $cache  Cache repository to store channel data
     */
    public function __construct(
        public readonly Repository $cache,
    ) {
        //
    }

    /**
     * Opens private channel
     *
     * @param  string  $channelId  Channel ID (e.g. "jobs.{uuid}")
     * @param  object  $notifiable  The notifiable instance
     * @param  DateTimeInterface|null  $expiresAt  When channel expires
     * @return array{channel:string, notifiable_class:string, notifiable_key:string, expires_at:DateTimeInterface} Channel data
     */
    public function open(string $channelId, object $notifiable, ?DateTimeInterface $expiresAt = null): array
    {
        $expiresAt = Carbon::instance($expiresAt ?? now()->addHours(3));

        $data = [
            'channel' => $channelId,
            'notifiable_class' => get_class($notifiable),
            'notifiable_key' => $this->getNotifiableKey($notifiable),
            'expires_at' => $expiresAt->toIso8601String(),
        ];

        if (! $this->cache->put($this->cacheKey($channelId), $data, $data['expires_at'])) {
            throw new \RuntimeException("Failed to open channel: {$channelId}");
        }

        return $data;
    }

    /**
     * Closes the channel.
     *
     * @param  string  $channelId  Channel ID (e.g. "jobs.{uuid}")
     */
    public function close(string $channelId): void
    {
        $this->cache->forget($this->cacheKey($channelId));
    }

    /**
     * Get the channel data.
     *
     * @param  string  $channelId  Channel ID (e.g. "jobs.{uuid}")
     * @return array{channel:string, notifiable_class:string, notifiable_key:string, expires_at:DateTimeInterface}|null Channel data or null if not found
     */
    public function get(string $channelId): ?array
    {
        $data = $this->cache->get($this->cacheKey($channelId));

        $expiresAt = isset($data['expires_at']) ? Carbon::parse($data['expires_at']) : null;

        if (is_null($data) || ($expiresAt && Carbon::now()->isAfter($expiresAt))) {
            $this->close($channelId);

            return null;
        }

        return [...$data, 'expires_at' => $expiresAt];
    }

    /**
     * Checks if channel exists and is valid.
     *
     * @param  string  $channelId  Channel ID (e.g. "jobs.{uuid}")
     */
    public function cacheKey(string $channelId): string
    {
        return "private_channel:{$channelId}";
    }

    /**
     * Get the notifiable key for the given notifiable.
     */
    public function getNotifiableKey(object $notifiable): string
    {
        $id = method_exists($notifiable, 'getKey') ? (string) $notifiable->getKey() : (string) $notifiable;

        return $id;
    }
}
