<?php

namespace SameOldNick\BackupManager\Tests\Unit;

use Illuminate\Queue\Middleware\WithoutOverlapping;
use SameOldNick\BackupManager\Jobs\BackupJob;
use SameOldNick\BackupManager\Jobs\CleanupJob;
use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Models\CleanupSchedule;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Testing\Concerns;
use SameOldNick\BackupManager\Tests\TestCase;

class ScheduleTest extends TestCase
{
    use Concerns\SchedulerTestHelpers;
    use Concerns\UiResponderAssertions;

    public function test_active_backup_schedule_records_are_scheduled(): void
    {
        $this->createBackupSchedule('Active Schedule', 'full', '0 0 * * *', true);
        $this->createBackupSchedule('Inactive Schedule', 'full', '0 1 * * *', false);

        $this->assertSchedulerJobs(function (array $jobs) {
            $this->assertCount(1, $jobs);
            $this->assertSame('0 0 * * *', $jobs[0]['expression']);
            $this->assertInstanceOf(BackupJob::class, $jobs[0]['job']);
            $this->assertSame(BackupJob::BACKUP_FULL, $jobs[0]['job']->backupType);
        });
    }

    public function test_backup_schedule_cron_shortcuts_are_transformed(): void
    {
        $this->createBackupSchedule('Shortcut Schedule', 'full', '@daily', true);

        $this->assertSchedulerJobs(function (array $jobs) {
            $this->assertCount(1, $jobs);
            $this->assertSame('0 0 * * *', $jobs[0]['expression']);
            $this->assertInstanceOf(BackupJob::class, $jobs[0]['job']);
            $this->assertSame(BackupJob::BACKUP_FULL, $jobs[0]['job']->backupType);
        });
    }

    public function test_full_backup_is_scheduled(): void
    {
        $this->createBackupSchedule('Full Backup', 'full', '0 2 * * *', true);

        $this->assertSchedulerJobs(function (array $jobs) {
            $this->assertCount(1, $jobs);
            $this->assertSame('0 2 * * *', $jobs[0]['expression']);
            $this->assertInstanceOf(BackupJob::class, $jobs[0]['job']);
            $this->assertSame(BackupJob::BACKUP_FULL, $jobs[0]['job']->backupType);
        });
    }

    public function test_databases_backup_is_scheduled(): void
    {
        $this->createBackupSchedule('Database Backup', 'databases', '0 3 * * *', true);

        $this->assertSchedulerJobs(function (array $jobs) {
            $this->assertCount(1, $jobs);
            $this->assertSame('0 3 * * *', $jobs[0]['expression']);
            $this->assertInstanceOf(BackupJob::class, $jobs[0]['job']);
            $this->assertSame(BackupJob::BACKUP_ONLY_DATABASES, $jobs[0]['job']->backupType);
        });
    }

    public function test_files_backup_is_scheduled(): void
    {
        $this->createBackupSchedule('Files Backup', 'files', '0 4 * * *', true);

        $this->assertSchedulerJobs(function (array $jobs) {
            $this->assertCount(1, $jobs);
            $this->assertSame('0 4 * * *', $jobs[0]['expression']);
            $this->assertInstanceOf(BackupJob::class, $jobs[0]['job']);
            $this->assertSame(BackupJob::BACKUP_ONLY_FILES, $jobs[0]['job']->backupType);
        });
    }

    public function test_scheduled_backup_jobs_receive_queue_configuration(): void
    {
        config()->set('backup-manager.jobs.backup', [
            'connection' => 'redis',
            'queue' => 'backups',
            'group' => 'nightly',
            'delay' => 30,
            'middleware' => [WithoutOverlapping::class],
            'after_commit' => true,
        ]);

        $this->createBackupSchedule('Queued Schedule', 'full', '0 0 * * *', true);

        $this->assertSchedulerJobs(function (array $jobs) {
            $this->assertCount(1, $jobs);

            $job = $jobs[0]['job'];

            $this->assertInstanceOf(BackupJob::class, $job);
            $this->assertSame('redis', $job->connection);
            $this->assertSame('backups', $job->queue);
            $this->assertSame('nightly', $job->messageGroup);
            $this->assertSame(30, $job->delay);
            $this->assertSame([WithoutOverlapping::class], $job->middleware);
            $this->assertTrue($job->afterCommit);
        });
    }

    public function test_scheduled_cleanup_jobs_receive_queue_configuration(): void
    {
        config()->set('backup-manager.jobs.cleanup', [
            'connection' => 'redis',
            'queue' => 'cleanups',
            'after_commit' => true,
        ]);

        $this->createCleanupSchedule('Queued Cleanup', '0 5 * * *', true);

        $this->assertSchedulerJobs(function (array $jobs) {
            $this->assertCount(1, $jobs);

            $job = $jobs[0]['job'];

            $this->assertInstanceOf(CleanupJob::class, $job);
            $this->assertSame('redis', $job->connection);
            $this->assertSame('cleanups', $job->queue);
            $this->assertTrue($job->afterCommit);
        });
    }

    public function test_active_cleanup_schedule_records_are_scheduled(): void
    {
        $this->createCleanupSchedule('Active Cleanup', '0 5 * * *', true);
        $this->createCleanupSchedule('Inactive Cleanup', '0 6 * * *', false);

        $this->assertSchedulerJobs(function (array $jobs) {
            $this->assertCount(1, $jobs);
            $this->assertSame('0 5 * * *', $jobs[0]['expression']);
            $this->assertInstanceOf(CleanupJob::class, $jobs[0]['job']);
        });
    }

    public function test_cleanup_schedule_cron_shortcuts_are_transformed(): void
    {
        $this->createCleanupSchedule('Shortcut Cleanup', '@daily', true);

        $this->assertSchedulerJobs(function (array $jobs) {
            $this->assertCount(1, $jobs);
            $this->assertSame('0 0 * * *', $jobs[0]['expression']);
            $this->assertInstanceOf(CleanupJob::class, $jobs[0]['job']);
        });
    }

    public function test_cleanup_schedule_is_scheduled(): void
    {
        $this->createCleanupSchedule('Cleanup Run', '0 7 * * *', true);

        $this->assertSchedulerJobs(function (array $jobs) {
            $this->assertCount(1, $jobs);
            $this->assertSame('0 7 * * *', $jobs[0]['expression']);
            $this->assertInstanceOf(CleanupJob::class, $jobs[0]['job']);
        });
    }

    public function test_schedule_backup_skips_configurations_with_invalid_morph_class(): void
    {
        $schedule = $this->createBackupSchedule('Schedule', 'full', '0 0 * * *', true);

        // Create a FilesystemConfiguration with a non-existent morph class
        $config = new FilesystemConfiguration([
            'name' => 'Broken Destination',
            'slug' => 'broken-destination',
            'disk_type' => 'local',
            'is_active' => true,
        ]);

        // Directly set the morph fields to a class that does not exist
        $config->forceFill([
            'configurable_type' => 'App\\Models\\NonExistentConfig',
            'configurable_id' => 999,
        ])->save();

        // Attach to the schedule via the pivot table
        $schedule->filesystemConfigurations()->attach($config);

        $this->assertSchedulerJobs(function (array $jobs) {
            $this->assertCount(1, $jobs);
            // The broken config should be skipped, falling back to default disk resolution
            $this->assertNull($jobs[0]['job']->disks);
        });
    }

    public function test_schedule_backup_skips_with_invalid_type(): void
    {
        $schedule1 = $this->createBackupSchedule('Valid Schedule', 'full', '0 0 * * *', true);
        $schedule2 = $this->createBackupSchedule('Invalid Schedule', 'full', '0 1 * * *', true);

        $schedule2->setRawAttributes(array_merge($schedule2->getAttributes(), ['type' => 'invalid_type']))->save();

        $this->assertSchedulerJobs(function (array $jobs) {
            $this->assertCount(1, $jobs);
            $this->assertSame('0 0 * * *', $jobs[0]['expression']);
            $this->assertInstanceOf(BackupJob::class, $jobs[0]['job']);
            $this->assertSame(BackupJob::BACKUP_FULL, $jobs[0]['job']->backupType);
        });
    }

    public function test_schedule_backup_skips_with_invalid_schedule(): void
    {
        $this->createBackupSchedule('Valid Schedule', 'full', '0 0 * * *', true);
        $this->createBackupSchedule('Invalid Schedule', 'full', 'invalid schedule', true);

        $this->assertSchedulerJobs(function (array $jobs) {
            $this->assertCount(1, $jobs);
            $this->assertSame('0 0 * * *', $jobs[0]['expression']);
            $this->assertInstanceOf(BackupJob::class, $jobs[0]['job']);
            $this->assertSame(BackupJob::BACKUP_FULL, $jobs[0]['job']->backupType);
        });
    }

    private function createBackupSchedule(string $name, string $type, string $cronExpression, bool $isActive): BackupSchedule
    {
        return BackupSchedule::create([
            'name' => $name,
            'type' => $type,
            'cron_expression' => $cronExpression,
            'is_active' => $isActive,
        ]);
    }

    private function createCleanupSchedule(string $name, string $cronExpression, bool $isActive): CleanupSchedule
    {
        return CleanupSchedule::create([
            'name' => $name,
            'cron_expression' => $cronExpression,
            'is_active' => $isActive,
        ]);
    }
}
