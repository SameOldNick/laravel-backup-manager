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
        bool $rethrowExceptions = true,
    ): BackupSchedulerRecorder {
        return new BackupSchedulerRecorder(
            $schedule ?? new Schedule,
            $eligibilityService ?? new ScheduleEligibilityService,
            $config ?? config('backup-manager.jobs', []),
            900,
            $attributes ?? config('backup-manager.scheduler.attributes', []),
            rethrowExceptions: $rethrowExceptions
        );
    }

    /**
     * Asserts that the BackupScheduler schedules the expected jobs and events.
     *
     * @param  callable(BackupSchedulerRecorder)  $callback  A callback function that receives the BackupScheduler instance for making assertions
     * @param  array|null  $attributes  The scheduler attributes to use
     * @param  bool  $rethrowExceptions  Whether to rethrow exceptions instead of handling them
     */
    protected function assertScheduler(
        callable $callback,
        ?array $attributes = null,
        bool $rethrowExceptions = true
    ): void {
        $scheduler = $this->makeSchedulerSpy(
            rethrowExceptions: $rethrowExceptions,
            attributes: $attributes
        );

        $scheduler->scheduleBackup();
        $scheduler->scheduleCleanup();

        $callback($scheduler);
    }
}
