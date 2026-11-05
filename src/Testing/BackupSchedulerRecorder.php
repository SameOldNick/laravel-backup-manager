<?php

namespace SameOldNick\BackupManager\Testing;

use Illuminate\Console\Scheduling\Event;
use SameOldNick\BackupManager\BackupScheduler;

class BackupSchedulerRecorder extends BackupScheduler
{
    public array $jobs = [];

    public array $events = [];

    /**
     * Overrides the scheduleJob method to record scheduled jobs
     *
     * @param  object  $job  The job instance to schedule
     * @param  string  $expression  The cron expression for scheduling the job
     * @return Event The scheduled event instance (not executed in this recorder)
     */
    protected function scheduleJob(object $job, string $expression)
    {
        $this->jobs[] = [
            'job' => $job,
            'expression' => $expression,
        ];

        $event = parent::scheduleJob($job, $expression);
        $this->events[] = $event;

        return $event;
    }

    /**
     * Overrides the handleOuterScheduleException method to throw the exception instead of handling it
     */
    protected function handleOuterScheduleException(\Throwable $e, string $scheduleType): void
    {
        throw $e;
    }

    /**
     * Overrides the handleScheduleException method to throw the exception instead of handling it
     */
    protected function handleScheduleException(
        \Throwable $e,
        string $scheduleType,
        int|string|null $scheduleId = null,
        string $scheduleName = 'Unknown Schedule',
    ): void {
        throw $e;
    }
}
