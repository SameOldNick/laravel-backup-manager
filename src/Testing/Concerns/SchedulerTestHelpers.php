<?php

namespace SameOldNick\BackupManager\Testing\Concerns;

use Illuminate\Console\Scheduling\Schedule;
use SameOldNick\BackupManager\BackupScheduler;
use SameOldNick\BackupManager\Services\ScheduleEligibilityService;

trait SchedulerTestHelpers
{
    /**
     * Creates a spy instance of the BackupScheduler class that records scheduled jobs and commands.
     *
     * @param  Schedule|null  $schedule  The schedule instance to use
     * @param  array|null  $config  The configuration array to use
     * @return object An instance of the anonymous class that extends BackupScheduler and records scheduled jobs and commands
     */
    protected function makeSchedulerSpy(?Schedule $schedule = null, ?ScheduleEligibilityService $eligibilityService = null, ?array $config = null): object
    {
        return new class($schedule ?? new Schedule, $eligibilityService ?? new ScheduleEligibilityService, $config ?? config('backup-manager.jobs', [])) extends BackupScheduler
        {
            public array $jobs = [];

            protected function scheduleJob(object $job, string $expression)
            {
                $this->jobs[] = [
                    'job' => $job,
                    'expression' => $expression,
                ];

                return null;
            }
        };
    }

    /**
     * Asserts that the expected jobs were scheduled by the BackupScheduler.
     *
     * @param  callable  $callback  A callback function that receives the array of scheduled jobs for making assertions
     */
    protected function assertSchedulerJobs(callable $callback): void
    {
        $scheduler = $this->makeSchedulerSpy();

        $scheduler->scheduleBackup();
        $scheduler->scheduleCleanup();

        $callback($scheduler->jobs);
    }
}
