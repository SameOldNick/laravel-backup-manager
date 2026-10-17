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
        #[Config('backup-manager.scheduler.error_ttl', 900)]
        protected readonly int $errorTtl = 900,
        #[Config('backup-manager.scheduler.backup', [])]
        protected readonly array $backupScheduleConfig = [],
        #[Config('backup-manager.scheduler.cleanup', [])]
        protected readonly array $cleanupScheduleConfig = []
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

                    $event = $this->scheduleJob($job, $eligible->expression)
                        ->description("Backup Schedule: {$schedule->name}");

                    $this->configureScheduleEvent($event, $this->backupScheduleConfig ?? []);
                } catch (\Throwable $e) {
                    $this->handleScheduleException(
                        $e,
                        'backup',
                        $schedule->getKey(),
                        $schedule->name,
                    );
                }
            }

        } catch (\Throwable $e) {
            $this->runAtMostOnceWithinTtl($this->createRunAtMostCacheKey('backup', null, get_class($e)), function () use ($e) {
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

                    $event = $this->scheduleJob($job, $eligible->expression)
                        ->description("Cleanup Schedule: {$schedule->name}");

                    $this->configureScheduleEvent($event, $this->cleanupScheduleConfig ?? []);
                } catch (\Throwable $e) {
                    $this->handleScheduleException(
                        $e,
                        'cleanup',
                        $schedule->getKey(),
                        $schedule->name,
                    );
                }
            }
        } catch (\Throwable $e) {
            $this->runAtMostOnceWithinTtl($this->createRunAtMostCacheKey('cleanup', null, get_class($e)), function () use ($e) {
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
     * @param  string  $scheduleName  The name of the schedule
     */
    protected function handleScheduleException(
        \Throwable $e,
        string $scheduleType,
        int|string|null $scheduleId = null,
        string $scheduleName = 'Unknown Schedule',
    ): void {
        $this->runAtMostOnceWithinTtl($this->createRunAtMostCacheKey($scheduleType, $scheduleId, get_class($e)), function () use ($e, $scheduleType, $scheduleId, $scheduleName) {
            Log::error($e->getMessage());

            event(new Events\ScheduleRegistrationFailed(
                scheduleType: $scheduleType,
                scheduleId: $scheduleId,
                scheduleName: $scheduleName,
                exception: $e,
            ));
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
     * Configures a scheduled event with the provided queue options.
     *
     * @param  Event  $event  The scheduled event to configure
     * @param  array  $queueOptions  The queue options to apply
     * @return Event The configured scheduled event
     */
    protected function configureScheduleEvent(Event $event, array $queueOptions): Event
    {
        if (empty($queueOptions)) {
            return $event;
        }

        if ($queueOptions['on_one_server'] ?? false) {
            $event->onOneServer();
        }

        $withoutOverlapping = $queueOptions['without_overlapping'] ?? ['enabled' => false];

        if ($withoutOverlapping['enabled'] ?? false) {
            $event->withoutOverlapping(
                $withoutOverlapping['expires_at'] ?? 1440,
                $withoutOverlapping['release_on_termination_signals'] ?? true
            );
        }

        if ($user = $queueOptions['user'] ?? null) {
            $event->user($user);
        }

        if ($environments = $queueOptions['environments'] ?? null) {
            $event->environments($environments);
        }

        if ($queueOptions['even_in_maintenance_mode'] ?? false) {
            $event->evenInMaintenanceMode();
        }

        if ($queueOptions['even_when_paused'] ?? false) {
            $event->evenWhenPaused();
        }

        if ($timezone = $queueOptions['timezone'] ?? null) {
            $event->timezone($timezone);
        }

        if ($when = $queueOptions['when'] ?? null) {
            $event->when(function (...$args) use ($when) {
                return app()->call($when, $args);
            });
        }

        return $event;
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
     * Creates a cache key for tracking the execution of a job.
     *
     * @param  string  $scheduleType  The type of schedule (backup or cleanup)
     * @param  string|int|null  $scheduleId  The ID of the schedule
     * @param  string|null  $suffix  An optional suffix to append to the key
     * @return string The generated cache key
     */
    protected function createRunAtMostCacheKey(string $scheduleType, string|int|null $scheduleId, ?string $suffix = null): string
    {
        $key = $scheduleId !== null
            ? "backup-manager:run:{$scheduleType}:{$scheduleId}"
            : "backup-manager:run:{$scheduleType}";

        if ($suffix !== null) {
            $key .= ":{$suffix}";
        }

        return $key;
    }

    /**
     * Runs a callback at most once within a specified time-to-live (TTL) period.
     *
     * @param  string  $key  The cache key to use for tracking the execution of the callback.
     * @param  callable()  $callback  The callback to execute.
     */
    protected function runAtMostOnceWithinTtl(string $key, callable $callback): void
    {
        if ($this->errorTtl > 0 && ! Cache::add($key, true, $this->errorTtl)) {
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
