<?php

namespace SameOldNick\BackupManager;

use Illuminate\Bus\Queueable;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use SameOldNick\BackupManager\Jobs\BackupJob;
use SameOldNick\BackupManager\Jobs\CleanupJob;
use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Models\CleanupSchedule;
use SameOldNick\BackupManager\Services\ScheduleEligibilityService;

class BackupScheduler
{
    /**
     * Initializes backup scheduler
     *
     * @param  Schedule  $schedule  The schedule instance to use for scheduling jobs and commands
     * @param  array  $config  The configuration array for backup and cleanup jobs
     * @return void
     */
    public function __construct(
        protected readonly Schedule $schedule,
        protected readonly ScheduleEligibilityService $eligibilityService,
        #[Config('backup-manager.jobs', [])]
        protected readonly array $config = [],
        #[Config('backup-manager.scheduler.log_ttl', 900)]
        protected readonly int $logTtl = 900
    ) {
        //
    }

    /**
     * Schedules backup and cleanup commands
     *
     * @return void
     */
    public function schedule()
    {
        $this->scheduleBackup();
        $this->scheduleCleanup();
    }

    /**
     * Schedules backup command
     *
     * @return void
     */
    public function scheduleBackup()
    {
        try {
            /** @var Builder<BackupSchedule> $scheduleQuery */
            $scheduleQuery = BackupSchedule::active()->with('filesystemConfigurations');

            $schedules = $scheduleQuery->get();

            foreach ($schedules as $schedule) {
                try {
                    $eligible = $this->eligibilityService->forBackup($schedule);

                    // Keep legacy schedules working by falling back to default disk resolution.
                    $job = $this->configureJob(
                        new BackupJob($schedule->type, $eligible->disks),
                        $this->config['backup'] ?? []
                    );

                    $this->scheduleJob($job, $eligible->expression);
                } catch (\Throwable $e) {
                    $this->handleScheduleException(
                        $e,
                        'backup',
                        $schedule->getKey(),
                    );
                }
            }

        } catch (\Throwable $e) {
            $this->logOnceWithinTtl('backup', null, function () use ($e) {
                Log::error('Error retrieving backup schedules: '.$e->getMessage());
            });
        }

    }

    /**
     * Schedules cleanup command
     *
     * @return void
     */
    public function scheduleCleanup()
    {
        try {
            /** @var Builder<CleanupSchedule> $scheduleQuery */
            $scheduleQuery = CleanupSchedule::active()->with('filesystemConfigurations');

            $schedules = $scheduleQuery->get();

            foreach ($schedules as $schedule) {
                try {
                    $eligible = $this->eligibilityService->forCleanup($schedule);

                    // Keep legacy schedules working by falling back to default disk resolution.
                    $job = $this->configureJob(
                        new CleanupJob($eligible->disks),
                        $this->config['cleanup'] ?? []
                    );

                    $this->scheduleJob($job, $eligible->expression);
                } catch (\Throwable $e) {
                    $this->handleScheduleException(
                        $e,
                        'cleanup',
                        $schedule->getKey(),
                    );
                }
            }
        } catch (\Throwable $e) {
            $this->logOnceWithinTtl('cleanup', null, function () use ($e) {
                Log::error('Error retrieving cleanup schedules: '.$e->getMessage());
            });
        }

    }

    /**
     * Handles exceptions that occur during the scheduling of backup or cleanup jobs, logging the error message and ensuring that the log is only written once within a specified time-to-live (TTL) period.
     *
     * @param  \Throwable  $e  The exception that occurred
     * @param  string  $scheduleType  The type of schedule (backup or cleanup)
     * @param  int|string|null  $scheduleId  The ID of the schedule
     */
    protected function handleScheduleException(
        \Throwable $e,
        string $scheduleType,
        int|string|null $scheduleId = null,
    ): void {
        $this->logOnceWithinTtl($scheduleType, $scheduleId, function () use ($e) {
            Log::error($e->getMessage());
        });
    }

    /**
     * Schedules job
     *
     * @return Event
     */
    protected function scheduleJob(object $job, string $expression)
    {
        return $this->schedule->job($job)->cron($expression);
    }

    /**
     * Schedules command
     *
     * @return Event
     */
    protected function scheduleCommand(string $command, string $expression)
    {
        return $this->schedule->command($command)->cron($expression)->runInBackground();
    }

    /**
     * Logs a message, ensuring that the log is only written once within a specified time-to-live (TTL) period.
     *
     * @param  string|int|null  $scheduleId  The schedule identifier.
     * @param  callable()  $callback  The callback to execute for logging.
     */
    protected function logOnceWithinTtl(string $scheduleType, string|int|null $scheduleId, callable $callback): void
    {
        $key =
            $scheduleId !== null ?
            "backup-manager:log:{$scheduleType}:{$scheduleId}" :
            "backup-manager:log:{$scheduleType}";

        if ($this->logTtl > 0 && ! Cache::add($key, true, $this->logTtl)) {
            return;
        }

        $callback();
    }

    /**
     * Configures a job with the provided queue options.
     *
     * @param  Queueable  $job  The job instance to configure
     * @param  array  $queueOptions  The queue options to apply
     * @return object The configured job instance
     */
    protected function configureJob(object $job, array $queueOptions): object
    {
        if (empty($queueOptions)) {
            return $job;
        }

        if ($queueOptions['connection'] ?? null) {
            $job->onConnection($queueOptions['connection']);
        }

        if ($queueOptions['queue'] ?? null) {
            $job->onQueue($queueOptions['queue']);
        }

        if ($queueOptions['group'] ?? null) {
            $job->onGroup($queueOptions['group']);
        }

        if ($queueOptions['delay'] ?? null) {
            $job->delay($queueOptions['delay']);
        }

        if ($queueOptions['middleware'] ?? null) {
            $job->through($queueOptions['middleware']);
        }

        if ($queueOptions['after_commit'] ?? null) {
            $job->afterCommit();
        }

        return $job;
    }
}
