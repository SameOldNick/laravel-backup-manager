<?php

namespace SameOldNick\BackupManager\Testing;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Container\Attributes\Config;
use SameOldNick\BackupManager\BackupScheduler;
use SameOldNick\BackupManager\Services\ScheduleEligibilityService;

class BackupSchedulerRecorder extends BackupScheduler
{
    public array $jobs = [];

    public array $events = [];

    public array $exceptions = [];

    /**
     * Create a new BackupSchedulerRecorder instance.
     *
     * @param  Schedule  $schedule  The schedule instance to use
     * @param  ScheduleEligibilityService  $eligibilityService  The schedule eligibility service to use
     * @param  array  $config  The jobs configuration array to use
     * @param  int  $errorTtl  The time-to-live (TTL) for error logging
     * @param  array  $scheduleAttributes  The scheduler attributes to use
     * @param  bool  $rethrowExceptions  Whether to rethrow exceptions instead of handling them
     */
    public function __construct(
        Schedule $schedule,
        ScheduleEligibilityService $eligibilityService,
        #[Config('backup-manager.jobs', [])]
        array $config = [],
        #[Config('backup-manager.scheduler.error_ttl', 900)]
        int $errorTtl = 900,
        #[Config('backup-manager.scheduler.attributes', [])]
        array $scheduleAttributes = [],
        public readonly bool $rethrowExceptions = true,
    ) {
        parent::__construct($schedule, $eligibilityService, $config, $errorTtl, $scheduleAttributes);
    }

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
        $this->exceptions[] = $e;

        if ($this->rethrowExceptions) {
            throw $e;
        }

        parent::handleOuterScheduleException($e, $scheduleType);
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
        $this->exceptions[] = $e;

        if ($this->rethrowExceptions) {
            throw $e;
        }

        parent::handleScheduleException($e, $scheduleType, $scheduleId, $scheduleName);
    }
}
