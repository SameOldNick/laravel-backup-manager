<?php

namespace SameOldNick\BackupManager\Broadcasting\Notifications;

use DateTimeInterface;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;

class JobStatusNotification extends BroadcastNotification implements ShouldBroadcastNow
{
    const STATUS_STARTED = 'started';

    const STATUS_FAILED = 'failed';

    const STATUS_COMPLETED = 'completed';

    /**
     * The event name to be broadcasted.
     */
    protected ?string $broadcastAs = 'job-status';

    /**
     * When notification was created.
     */
    public readonly DateTimeInterface $dateTime;

    /**
     * Creates new JobNotification.
     *
     * @param  string  $channel  Channel to broadcast on
     * @param  string  $status  Job status
     * @param  DateTimeInterface|null  $dateTime  When the job completed. If null, the current date/time is used.
     */
    public function __construct(
        string $channel,
        public readonly string $status,
        ?DateTimeInterface $dateTime = null,
        public readonly array $extra = []
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
