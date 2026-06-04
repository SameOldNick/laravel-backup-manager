<?php

namespace SameOldNick\BackupManager\Broadcasting\Notifications;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

abstract class BroadcastNotification extends Notification
{
    use Queueable;

    /**
     * Whether to broadcast on a private channel. If false, the event is broadcasted on a public channel.
     */
    protected bool $broadcastOnPrivateChannel = true;

    /**
     * The channel to broadcast on. If empty, the event is broadcasted on the 'App.Models.User.{id}' private channel.
     */
    protected ?string $broadcastOn = null;

    /**
     * The event name to be broadcasted.
     */
    protected ?string $broadcastAs = null;

    /**
     * The type of event being broadcasted.
     */
    protected ?string $broadcastType = null;

    /**
     * The queue connection used for broadcasting the notification event.
     */
    protected ?string $broadcastConnection = 'sync';

    /**
     * The queue used for broadcasting the notification event.
     */
    protected ?string $broadcastQueue = null;

    /**
     * Get the notification's delivery channels.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }

    /**
     * Gets the event name.
     * This is the 'event' field sent to the websocket.
     */
    public function broadcastAs(): string
    {
        return $this->broadcastAs ?: get_class($this);
    }

    /**
     * Get the type of the notification being broadcast.
     * This is included with the event data.
     *
     * @return string
     */
    public function broadcastType()
    {
        return $this->broadcastType ?: get_class($this);
    }

    /**
     * Get the channels the event should broadcast on.
     * If empty is returned, the event is broadcasted on the the 'App.Models.User.{id}' private channel.
     *
     * @return Channel|Channel[]
     */
    public function broadcastOn()
    {
        if ($broadcastOn = $this->broadcastOn) {
            return $this->broadcastOnPrivateChannel
                ? new PrivateChannel($broadcastOn)
                : new Channel($broadcastOn);
        }

        return [];
    }

    /**
     * Create a broadcast message with the notification's preferred queue configuration.
     *
     * @param  array<string, mixed>  $data
     */
    protected function makeBroadcastMessage(array $data): BroadcastMessage
    {
        $message = new BroadcastMessage($data);

        if (! is_null($this->broadcastConnection)) {
            $message->onConnection($this->broadcastConnection);
        }

        if (! is_null($this->broadcastQueue)) {
            $message->onQueue($this->broadcastQueue);
        }

        return $message;
    }

    /**
     * Get the notification data to broadcast.
     *
     * @return BroadcastMessage|array<string, mixed>
     */
    abstract public function toBroadcast(object $notifiable): BroadcastMessage|array;
}
