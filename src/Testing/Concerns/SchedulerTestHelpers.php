<?php

namespace SameOldNick\BackupManager\Testing\Concerns;

use Illuminate\Console\Scheduling\Schedule;
use SameOldNick\BackupManager\BackupScheduler;
use SameOldNick\BackupManager\Services\ScheduleEligibilityService;
use SameOldNick\BackupManager\Testing\BackupSchedulerRecorder;

trait SchedulerTestHelpers
{
    /**
     * Creates a spy instance of the BackupScheduler class that records scheduled jobs and events.
     *
     * @param  Schedule|null  $schedule  The schedule instance to use
     * @param  ScheduleEligibilityService|null  $eligibilityService  The schedule eligibility service to use
     * @param  array|null  $config  The jobs configuration array to use
     * @param  array|null  $attributes  The scheduler attributes to use
     * @return BackupSchedulerRecorder A spy instance of the BackupScheduler class
     */
    protected function makeSchedulerSpy(
        ?Schedule $schedule = null,
        ?ScheduleEligibilityService $eligibilityService = null,
        ?array $config = null,
        ?array $attributes = null,
    ): BackupSchedulerRecorder {
        return new BackupSchedulerRecorder(
            $schedule ?? new Schedule,
            $eligibilityService ?? new ScheduleEligibilityService,
            $config ?? config('backup-manager.jobs', []),
            900,
            $attributes ?? config('backup-manager.scheduler.attributes', [])
        );
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

    /**
     * Asserts that the expected events were scheduled by the BackupScheduler.
     *
     * @param  callable  $callback  A callback function that receives the array of scheduled events for making assertions
     * @param  array|null  $attributes  The scheduler attributes to use
     */
    protected function assertSchedulerEvents(
        callable $callback,
        ?array $attributes = null,
    ): void {
        $scheduler = $this->makeSchedulerSpy(
            attributes: $attributes
        );

        $scheduler->scheduleBackup();
        $scheduler->scheduleCleanup();

        $callback($scheduler->events);
    }
}
