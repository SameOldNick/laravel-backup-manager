<?php

namespace SameOldNick\BackupManager;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\Facades\Schedule;
use SameOldNick\BackupManager\Concerns\TransformsCronExpression;
use SameOldNick\BackupManager\Enums\BackupTypes;
use SameOldNick\BackupManager\Jobs\BackupJob;
use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Models\CleanupSchedule;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;

class BackupScheduler
{
    use TransformsCronExpression;

    /**
     * Initializes backup scheduler
     */
    public function __construct()
    {
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
        $scheduleQuery = BackupSchedule::active()->with('filesystemConfigurations');

        $schedules = $scheduleQuery->get();

        foreach ($schedules as $schedule) {
            $expression = $this->transformCronExpression($schedule->cron_expression);
            $backupType = match ($schedule->type) {
                BackupTypes::Databases => BackupJob::BACKUP_ONLY_DATABASES,
                BackupTypes::Files => BackupJob::BACKUP_ONLY_FILES,
                default => BackupJob::BACKUP_FULL,
            };

            $disks = $schedule->filesystemConfigurations
                ->filter(fn (FilesystemConfiguration $config) => $config->is_active)
                ->map(fn (FilesystemConfiguration $config) => $config->driver_name)
                ->values()
                ->all();

            // Keep legacy schedules working by falling back to default disk resolution.
            $this->scheduleJob(new BackupJob($backupType, count($disks) > 0 ? $disks : null), $expression);
        }
    }

    /**
     * Schedules cleanup command
     *
     * @return void
     */
    public function scheduleCleanup()
    {
        $expressions = CleanupSchedule::active()->pluck('cron_expression')->toArray();

        foreach ($expressions as $expression) {
            $expression = $this->transformCronExpression($expression);

            $this->scheduleCommand('backup:clean', $expression);
        }
    }

    /**
     * Schedules job
     *
     * @return Event
     */
    protected function scheduleJob(object $job, string $expression)
    {
        return Schedule::job($job)->cron($expression);
    }

    /**
     * Schedules command
     *
     * @return Event
     */
    protected function scheduleCommand(string $command, string $expression)
    {
        return Schedule::command($command)->cron($expression)->runInBackground();
    }
}
