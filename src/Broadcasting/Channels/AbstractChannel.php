<?php

namespace SameOldNick\BackupManager\Broadcasting\Channels;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use SameOldNick\BackupManager\Broadcasting\Access\ChannelAccessManager;

abstract class AbstractChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct(
        public readonly ChannelAccessManager $manager,
    ) {
        //
    }

    /**
     * Get the name of the channel.
     */
    abstract public static function getChannelName(): string;

    /**
     * Authenticate the user's access to the channel.
     */
    public function join($user, string $uuid): array|bool
    {
        return $this->hasAccess($user, static::getChannelName(), $uuid);
    }

    /**
     * Determines if the given user has access to the channel with the specified ID.
     *
     * @param  mixed  $user  The user to check access for (null if not authenticated)
     * @param  string  $channel  The channel name (e.g. "jobs")
     * @param  string  $id  The channel ID (e.g. "{uuid}")
     * @return bool True if the user has access, false otherwise
     */
    protected function hasAccess($user, string $channel, string $id): bool
    {
        if (is_null($user)) {
            return false;
        }

        $channelId = $this->manager->createChannelId($channel, $id);

        if (! $lease = $this->manager->get($channelId)) {
            Log::warning("Channel not found: {$channelId}", [
                'channel' => $channel,
                'id' => $id,
            ]);

            return false;
        }

        $notifiable = $lease->getNotifiable();

        return $notifiable !== null && $this->isNotifiable($notifiable, $user);
    }

    /**
     * Determines if the expected notifiable instance matches the given notifiable instance.
     *
     * @param  object  $expected  The expected notifiable instance (e.g. from channel lease)
     * @param  object  $notifiable  The notifiable instance to compare against (e.g. authenticated user)
     * @return bool True if the instances match, false otherwise
     */
    protected function isNotifiable(object $expected, object $notifiable): bool
    {
        if ($expected instanceof Model && $notifiable instanceof Model) {
            return $notifiable->is($expected);
        }

        if (method_exists($expected, 'getKey') && method_exists($notifiable, 'getKey')) {
            return get_class($expected) === get_class($notifiable) && $expected->getKey() === $notifiable->getKey();
        }

        // Fallback to strict equality check if no better method is available
        return $expected === $notifiable;
    }
}
