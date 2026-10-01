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

    public function test_cleanup_schedule_without_filesystem_configurations_is_scheduled_with_all_disks(): void
    {
        $this->createCleanupSchedule('Cleanup Run', '0 7 * * *', true);

        $this->assertSchedulerJobs(function (array $jobs) {
            $this->assertCount(1, $jobs);
            $this->assertInstanceOf(CleanupJob::class, $jobs[0]['job']);
            // No configurations attached: keep the legacy behaviour of cleaning every disk.
            $this->assertNull($jobs[0]['job']->disks);
        });
    }

    public function test_cleanup_schedule_with_active_and_valid_filesystem_configurations_is_scheduled_with_those_disks(): void
    {
        $schedule = $this->createCleanupSchedule('Cleanup Run', '0 7 * * *', true);
        $destination = $this->createDestination('Active Destination');

        $schedule->filesystemConfigurations()->attach($destination);

        $this->assertSchedulerJobs(function (array $jobs) use ($destination) {
            $this->assertCount(1, $jobs);
            $this->assertInstanceOf(CleanupJob::class, $jobs[0]['job']);
            $this->assertSame([$destination->driver_name], $jobs[0]['job']->disks);
        });
    }

    public function test_cleanup_schedule_ignores_inactive_filesystem_configurations(): void
    {
        $schedule = $this->createCleanupSchedule('Cleanup Run', '0 7 * * *', true);
        $active = $this->createDestination('Active Destination');
        $inactive = $this->createDestination('Inactive Destination', isActive: false);

        $schedule->filesystemConfigurations()->attach([$active->id, $inactive->id]);

        $this->assertSchedulerJobs(function (array $jobs) use ($active, $inactive) {
            $this->assertCount(1, $jobs);
            $this->assertInstanceOf(CleanupJob::class, $jobs[0]['job']);
            $this->assertSame([$active->driver_name], $jobs[0]['job']->disks);
            $this->assertNotContains($inactive->driver_name, $jobs[0]['job']->disks);
        });
    }

    public function test_cleanup_schedule_ignores_invalid_filesystem_configurations(): void
    {
        $schedule = $this->createCleanupSchedule('Cleanup Run', '0 7 * * *', true);
        $valid = $this->createDestination('Valid Destination');
        $invalid = $this->createDestination('Invalid Destination', isValid: false);

        $schedule->filesystemConfigurations()->attach([$valid->id, $invalid->id]);

        $this->assertSchedulerJobs(function (array $jobs) use ($valid, $invalid) {
            $this->assertCount(1, $jobs);
            $this->assertInstanceOf(CleanupJob::class, $jobs[0]['job']);
            $this->assertSame([$valid->driver_name], $jobs[0]['job']->disks);
            $this->assertNotContains($invalid->driver_name, $jobs[0]['job']->disks);
        });
    }

    public function test_cleanup_schedule_with_only_inactive_filesystem_configurations_is_not_scheduled(): void
    {
        // A schedule whose attached destinations are all inactive must not be scheduled...
        $skipped = $this->createCleanupSchedule('Skipped Cleanup', '0 7 * * *', true);
        $skipped->filesystemConfigurations()->attach(
            $this->createDestination('Inactive Destination', isActive: false)
        );

        // ...but it must not stop the other schedules from being scheduled.
        $kept = $this->createCleanupSchedule('Kept Cleanup', '0 8 * * *', true);
        $active = $this->createDestination('Active Destination');
        $kept->filesystemConfigurations()->attach($active);

        $this->assertSchedulerJobs(function (array $jobs) use ($active) {
            $this->assertCount(1, $jobs);
            $this->assertSame('0 8 * * *', $jobs[0]['expression']);
            $this->assertInstanceOf(CleanupJob::class, $jobs[0]['job']);
            $this->assertSame([$active->driver_name], $jobs[0]['job']->disks);
        });
    }

    public function test_backup_schedule_without_filesystem_configurations_uses_all_disks(): void
    {
        $this->createBackupSchedule('Backup Run', 'full', '0 0 * * *', true);

        $this->assertSchedulerJobs(function (array $jobs) {
            $this->assertCount(1, $jobs);
            $this->assertInstanceOf(BackupJob::class, $jobs[0]['job']);
            $this->assertNull($jobs[0]['job']->disks);
        });
    }

    public function test_schedule_backup_skips_configurations_with_invalid_morph_class(): void
    {
        // A schedule whose only destination has a non-existent morph class must not be scheduled...
        $skipped = $this->createBackupSchedule('Broken Schedule', 'full', '0 1 * * *', true);
        $skipped->filesystemConfigurations()->attach(
            $this->createDestination('Broken Destination', isValid: false)
        );

        // ...but it must not stop the other schedules from being scheduled.
        $kept = $this->createBackupSchedule('Valid Schedule', 'full', '0 0 * * *', true);
        $valid = $this->createDestination('Valid Destination');
        $kept->filesystemConfigurations()->attach($valid);

        $this->assertSchedulerJobs(function (array $jobs) use ($valid) {
            $this->assertCount(1, $jobs);
            $this->assertSame('0 0 * * *', $jobs[0]['expression']);
            $this->assertInstanceOf(BackupJob::class, $jobs[0]['job']);
            $this->assertSame([$valid->driver_name], $jobs[0]['job']->disks);
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

    /**
     * Create a filesystem configuration (destination) for scheduling tests.
     */
    private function createDestination(string $name, bool $isActive = true, bool $isValid = true): FilesystemConfiguration
    {
        $destination = FilesystemConfiguration::factory()->local()->create([
            'name' => $name,
            'is_active' => $isActive,
        ]);

        if (! $isValid) {
            // Point the configuration at a configurable class that does not exist.
            $destination->forceFill([
                'configurable_type' => 'App\\Models\\NonExistentConfig',
            ])->save();
        }

        return $destination;
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
