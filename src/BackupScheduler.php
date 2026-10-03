<?php

namespace SameOldNick\BackupManager;

use Illuminate\Bus\Queueable;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use SameOldNick\BackupManager\Concerns\TransformsCronExpression;
use SameOldNick\BackupManager\Jobs\BackupJob;
use SameOldNick\BackupManager\Jobs\CleanupJob;
use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Models\CleanupSchedule;
use SameOldNick\BackupManager\Models\Collections\FilesystemConfigurationCollection;

class BackupScheduler
{
    use TransformsCronExpression;

    /**
     * Initializes backup scheduler
     *
     * @param  Schedule  $schedule  The schedule instance to use for scheduling jobs and commands
     * @param  array  $config  The configuration array for backup and cleanup jobs
     * @return void
     */
    public function __construct(
        protected readonly Schedule $schedule,
        #[Config('backup-manager.jobs', [])]
        protected readonly array $config = []
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
                    $expression = $this->transformCronExpression($schedule->cron_expression);

                    // Skip schedules with invalid cron expressions
                    if (! $expression) {
                        Log::error("Invalid cron expression for backup schedule '{$schedule->name}': '{$schedule->cron_expression}'");

                        continue;
                    }

                    // Skip schedules with invalid backup types
                    if (! $backupType = $schedule->type) {
                        Log::error("Invalid backup type for schedule '{$schedule->name}': '{$schedule->getRawOriginal('type')}'");

                        continue;
                    }

                    $disks = $this->getDisksForJob($schedule->filesystemConfigurations);

                    if ($disks === []) {
                        Log::error("No valid disks found for backup schedule '{$schedule->name}' with specified disks.");

                        continue;
                    }

                    // Keep legacy schedules working by falling back to default disk resolution.
                    $job = $this->configureJob(
                        new BackupJob($backupType, $disks),
                        $this->config['backup'] ?? []
                    );

                    $this->scheduleJob($job, $expression);
                } catch (\Throwable $e) {
                    $name = $schedule->getRawOriginal('name') ?? 'Unknown Schedule';
                    Log::error("Error scheduling backup for schedule '{$name}': ".$e->getMessage());
                }
            }

        } catch (\Throwable $e) {
            Log::error('Error retrieving backup schedules: '.$e->getMessage());
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
                    $expression = $this->transformCronExpression($schedule->cron_expression);

                    // Skip schedules with invalid cron expressions
                    if (! $expression) {
                        Log::error("Invalid cron expression for cleanup schedule '{$schedule->name}': '{$schedule->cron_expression}'");

                        continue;
                    }

                    $disks = $this->getDisksForJob($schedule->filesystemConfigurations);

                    if ($disks === []) {
                        Log::error("No valid disks found for cleanup schedule '{$schedule->name}' with specified disks.");

                        continue;
                    }

                    // Keep legacy schedules working by falling back to default disk resolution.
                    $job = $this->configureJob(
                        new CleanupJob($disks),
                        $this->config['cleanup'] ?? []
                    );

                    $this->scheduleJob($job, $expression);
                } catch (\Throwable $e) {
                    $name = $schedule->getRawOriginal('name') ?? 'Unknown Schedule';
                    Log::error("Error scheduling cleanup for schedule '{$name}': ".$e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error retrieving cleanup schedules: '.$e->getMessage());
        }

    }

    /**
     * Gets disks for job based on the provided FilesystemConfigurationCollection.
     *
     * @param  FilesystemConfigurationCollection  $configs  The collection of filesystem configurations associated with the schedule.
     * @return ?array<int, string> Returns an array of disk names or null if no specific disks are configured.
     */
    protected function getDisksForJob(FilesystemConfigurationCollection $configs): ?array
    {
        if ($configs->isEmpty()) {
            return null; // No specific disks configured, use default behavior
        }

        $disks = $configs->getActiveAndValid()->toDiskNames();

        // If there are supposed to be disks for this schedule, but none were found, return an empty array.
        if (count($disks) === 0) {
            return [];
        }

        return $disks;
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
