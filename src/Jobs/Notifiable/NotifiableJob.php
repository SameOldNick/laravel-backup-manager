<?php

namespace SameOldNick\BackupManager\Jobs\Notifiable;

use SameOldNick\BackupManager\Broadcasting\Access\ChannelAccessManager;
use SameOldNick\BackupManager\Broadcasting\Notifiers\JobStatusNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

abstract class NotifiableJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * In order for the job to be serialized for later processing by the queue:
     *  - The properties cannot be set as readonly.
     *  - The properties must have a visibility of at least protected (not private).
     */
    protected string $channel;

    /**
     * The notifiable to send notifications to
     */
    protected object $notifiable;

    protected ?JobStatusNotifier $jobStatusNotifier = null;

    /**
     * Create a new job instance.
     */
    public function __construct(string $channel, object $notifiable)
    {
        $this->channel = $channel;
        $this->notifiable = $notifiable;
    }

    /**
     * A wrapper for performing the job that handles sending notifications about the job status.
     *
     * @param  callable  $callback  The actual job logic to perform
     */
    protected function performJob(callable $callback): void
    {
        $this->getJobStatusNotifier()->start();

        try {
            $callback();

            $this->getJobStatusNotifier()->completed();
        } finally {
            $this->closeChannel();
        }
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $this->getJobStatusNotifier()->failed($exception);

        $this->closeChannel();
    }

    /**
     * Gets the job status notifier
     */
    public function getJobStatusNotifier(): JobStatusNotifier
    {
        if ($this->jobStatusNotifier === null) {
            $this->jobStatusNotifier = new JobStatusNotifier($this->channel, $this->notifiable);
        }

        return $this->jobStatusNotifier;
    }

    /**
     * Closes the broadcasting channel associated with this job to prevent further notifications from being sent.
     */
    protected function closeChannel(): void
    {
        app(ChannelAccessManager::class)->close($this->channel);
    }
}
