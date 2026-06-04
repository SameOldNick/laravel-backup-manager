<?php

namespace SameOldNick\BackupManager\Broadcasting\Notifications;

use DateTimeInterface;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ProcessStatusNotification extends BroadcastNotification implements ShouldBroadcastNow
{
    const STATUS_BEGIN = 'begin';

    const STATUS_COMPLETE = 'complete';

    /**
     * The event name to be broadcasted.
     */
    protected ?string $broadcastAs = 'process-status';

    /**
     * When notification was created.
     */
    public readonly DateTimeInterface $dateTime;

    /**
     * Creates new ProcessStatusNotification.
     *
     * @param  string  $channel  Channel
     * @param  string  $status  Process status
     * @param  DateTimeInterface|null  $dateTime  When the process completed. If null, the current date/time is used.
     */
    public function __construct(
        string $channel,
        public readonly string $status,
        ?DateTimeInterface $dateTime = null,
        public readonly array $extra = [],
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
            'status' => $this->status,
            'extra' => $this->extra,
        ]);
    }
}
