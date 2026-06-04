<?php

namespace SameOldNick\BackupManager\Broadcasting\Notifications;

use DateTimeInterface;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ProcessOutputNotification extends BroadcastNotification implements ShouldBroadcastNow
{
    /**
     * The event name to be broadcasted.
     */
    protected ?string $broadcastAs = 'process-output';

    /**
     * When notification was created.
     */
    public readonly DateTimeInterface $dateTime;

    /**
     * Creates new ProcessOutputNotification.
     *
     * @param  string  $channel  Channel to broadcast on
     * @param  string  $message  Output message
     * @param  bool  $newline  Whether the message ends with a newline character
     * @param  DateTimeInterface|null  $dateTime  When the process completed. If null, the current date/time is used.
     */
    public function __construct(
        string $channel,
        public readonly string $message,
        public readonly bool $newline,
        ?DateTimeInterface $dateTime = null
    ) {
        $this->broadcastOn = $channel;
        $this->dateTime = $dateTime ?? now();
    }

    /**
     * {@inheritDoc}
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return $this->makeBroadcastMessage([
            'date_time' => $this->dateTime,
            'message' => $this->message,
            'newline' => $this->newline,
        ]);
    }
}
