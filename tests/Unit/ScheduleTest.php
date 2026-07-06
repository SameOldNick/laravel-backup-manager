<?php

namespace SameOldNick\BackupManager\Tests\Unit;

use SameOldNick\BackupManager\Jobs\BackupJob;
use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Models\CleanupSchedule;
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

    public function test_active_cleanup_schedule_records_are_scheduled(): void
    {
        $this->createCleanupSchedule('Active Cleanup', '0 5 * * *', true);
        $this->createCleanupSchedule('Inactive Cleanup', '0 6 * * *', false);

        $this->assertSchedulerCommands(function (array $commands) {
            $this->assertCount(1, $commands);
            $this->assertSame('0 5 * * *', $commands[0]['expression']);
            $this->assertSame('backup:clean', $commands[0]['command']);
        });
    }

    public function test_cleanup_schedule_cron_shortcuts_are_transformed(): void
    {
        $this->createCleanupSchedule('Shortcut Cleanup', '@daily', true);

        $this->assertSchedulerCommands(function (array $commands) {
            $this->assertCount(1, $commands);
            $this->assertSame('0 0 * * *', $commands[0]['expression']);
            $this->assertSame('backup:clean', $commands[0]['command']);
        });
    }

    public function test_cleanup_schedule_is_scheduled(): void
    {
        $this->createCleanupSchedule('Cleanup Run', '0 7 * * *', true);

        $this->assertSchedulerCommands(function (array $commands) {
            $this->assertCount(1, $commands);
            $this->assertSame('0 7 * * *', $commands[0]['expression']);
            $this->assertSame('backup:clean', $commands[0]['command']);
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
