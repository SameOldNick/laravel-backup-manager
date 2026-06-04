<?php

namespace SameOldNick\BackupManager\Broadcasting\Notifiers;

use SameOldNick\BackupManager\Broadcasting\Notifications\JobStatusNotification;
use DateTimeInterface;
use Throwable;

class JobStatusNotifier extends AbstractNotifier
{
    /**
     * Sends notification that job was started.
     *
     * @return void
     */
    public function start(?DateTimeInterface $dateTime = null)
    {
        $this->notify(
            $this->notifiable,
            new JobStatusNotification($this->channel, JobStatusNotification::STATUS_STARTED, $dateTime)
        );
    }

    /**
     * Sends notification that job failed.
     *
     * @return void
     */
    public function failed(Throwable $exception, ?DateTimeInterface $dateTime = null)
    {
        // The exception message can only be used because serializing \Exception causes error "Serialization of 'Closure' is not allowed"
        $this->notify(
            $this->notifiable,
            new JobStatusNotification($this->channel, JobStatusNotification::STATUS_FAILED, $dateTime, ['exception' => $exception->getMessage()])
        );
    }

    /**
     * Sends notification that job completed.
     *
     * @return void
     */
    public function completed(?DateTimeInterface $dateTime = null)
    {
        $this->notify(
            $this->notifiable,
            new JobStatusNotification($this->channel, JobStatusNotification::STATUS_COMPLETED, $dateTime)
        );
    }
}
