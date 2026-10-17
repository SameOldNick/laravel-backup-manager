<?php

namespace SameOldNick\BackupManager\Testing;

use SameOldNick\BackupManager\BackupScheduler;

class BackupSchedulerRecorder extends BackupScheduler
{
    public array $jobs = [];

    /**
     * Overrides the scheduleJob method to record scheduled jobs instead of executing them.
     *
     * @param  object  $job  The job instance to schedule
     * @param  string  $expression  The cron expression for scheduling the job
     * @return void
     */
    protected function scheduleJob(object $job, string $expression)
    {
        $this->jobs[] = [
            'job' => $job,
            'expression' => $expression,
        ];

        return null;
    }
}
