<?php

namespace SameOldNick\BackupManager\Tests\Feature;

use SameOldNick\BackupManager\BackupScheduler;
use SameOldNick\BackupManager\Jobs\BackupJob;
use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Models\CleanupSchedule;
use SameOldNick\BackupManager\Testing\Concerns;
use SameOldNick\BackupManager\Tests\TestCase;

class ScheduleTest extends TestCase
{
    use Concerns\UiResponderAssertions;

    public function test_active_backup_schedule_records_are_scheduled(): void
    {
        $this->createBackupSchedule('Active Schedule', 'full', '0 0 * * *', true);
        $this->createBackupSchedule('Inactive Schedule', 'full', '0 1 * * *', false);

        $scheduler = $this->makeSchedulerSpy();

        $scheduler->scheduleBackup();

        $this->assertCount(1, $scheduler->jobs);
    }

    public function test_backup_schedule_cron_shortcuts_are_transformed(): void
    {
        $this->createBackupSchedule('Shortcut Schedule', 'full', '@daily', true);

        $scheduler = $this->makeSchedulerSpy();

        $scheduler->scheduleBackup();

        $this->assertSame('0 0 * * *', $scheduler->jobs[0]['expression']);
    }

    public function test_full_backup_is_scheduled(): void
    {
        $this->createBackupSchedule('Full Backup', 'full', '0 2 * * *', true);

        $scheduler = $this->makeSchedulerSpy();
        $scheduler->scheduleBackup();

        $this->assertInstanceOf(BackupJob::class, $scheduler->jobs[0]['job']);
        $this->assertSame(BackupJob::BACKUP_FULL, $scheduler->jobs[0]['job']->backupType);
    }

    public function test_databases_backup_is_scheduled(): void
    {
        $this->createBackupSchedule('Database Backup', 'databases', '0 3 * * *', true);

        $scheduler = $this->makeSchedulerSpy();
        $scheduler->scheduleBackup();

        $this->assertSame(BackupJob::BACKUP_ONLY_DATABASES, $scheduler->jobs[0]['job']->backupType);
    }

    public function test_files_backup_is_scheduled(): void
    {
        $this->createBackupSchedule('Files Backup', 'files', '0 4 * * *', true);

        $scheduler = $this->makeSchedulerSpy();
        $scheduler->scheduleBackup();

        $this->assertSame(BackupJob::BACKUP_ONLY_FILES, $scheduler->jobs[0]['job']->backupType);
    }

    public function test_active_cleanup_schedule_records_are_scheduled(): void
    {
        $this->createCleanupSchedule('Active Cleanup', '0 5 * * *', true);
        $this->createCleanupSchedule('Inactive Cleanup', '0 6 * * *', false);

        $scheduler = $this->makeSchedulerSpy();

        $scheduler->scheduleCleanup();

        $this->assertCount(1, $scheduler->commands);
    }

    public function test_cleanup_schedule_cron_shortcuts_are_transformed(): void
    {
        $this->createCleanupSchedule('Shortcut Cleanup', '@daily', true);

        $scheduler = $this->makeSchedulerSpy();

        $scheduler->scheduleCleanup();

        $this->assertSame('0 0 * * *', $scheduler->commands[0]['expression']);
    }

    public function test_cleanup_schedule_is_scheduled(): void
    {
        $this->createCleanupSchedule('Cleanup Run', '0 7 * * *', true);

        $scheduler = $this->makeSchedulerSpy();
        $scheduler->scheduleCleanup();

        $this->assertSame('backup:clean', $scheduler->commands[0]['command']);
    }

    private function makeSchedulerSpy(): object
    {
        return new class extends BackupScheduler
        {
            public array $jobs = [];

            public array $commands = [];

            protected function scheduleJob($job, string $expression)
            {
                $this->jobs[] = [
                    'job' => $job,
                    'expression' => $expression,
                ];

                return null;
            }

            protected function scheduleCommand(string $command, string $expression)
            {
                $this->commands[] = [
                    'command' => $command,
                    'expression' => $expression,
                ];

                return null;
            }
        };
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
