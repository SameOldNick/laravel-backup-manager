<?php

namespace SameOldNick\BackupManager\Services;

use SameOldNick\BackupManager\Concerns\TransformsCronExpression;
use SameOldNick\BackupManager\DataTransferObjects\EligibleSchedule;
use SameOldNick\BackupManager\Exceptions\InvalidBackupTypeException;
use SameOldNick\BackupManager\Exceptions\InvalidCronExpressionException;
use SameOldNick\BackupManager\Exceptions\NoValidDisksException;
use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Models\CleanupSchedule;
use SameOldNick\BackupManager\Models\Collections\FilesystemConfigurationCollection;

class ScheduleEligibilityService
{
    use TransformsCronExpression;

    /**
     * Determines if a backup schedule is eligible to run based on its cron expression and associated disks.
     *
     * @param  BackupSchedule  $schedule  The backup schedule to evaluate.
     * @return EligibleSchedule An object containing the cron expression and eligible disks for the schedule.
     *
     * @throws InvalidCronExpressionException If the cron expression is invalid.
     * @throws NoValidDisksException If no valid disks are found for the schedule.
     */
    public function forBackup(BackupSchedule $schedule): EligibleSchedule
    {
        $expression = $this->transformCronExpression($schedule->cron_expression);

        // Skip schedules with invalid cron expressions
        if (! $expression) {
            throw new InvalidCronExpressionException("Invalid cron expression for backup schedule '{$schedule->name}': '{$schedule->cron_expression}'");
        }

        // Skip schedules with invalid backup types
        if (! $schedule->type) {
            throw new InvalidBackupTypeException("Invalid backup type for schedule '{$schedule->name}': '{$schedule->getRawOriginal('type')}'");
        }

        $disks = $this->getDisksForJob($schedule->filesystemConfigurations);

        if ($schedule->filesystemConfigurations->isNotEmpty() && $disks === []) {
            throw new NoValidDisksException("No valid disks found for backup schedule '{$schedule->name}' with specified disks.");
        }

        return new EligibleSchedule($expression, $disks);
    }

    /**
     * Determines the eligibility of a cleanup schedule for execution.
     *
     * @param  CleanupSchedule  $schedule  The cleanup schedule instance to evaluate.
     * @return EligibleSchedule Returns an EligibleSchedule object containing the cron expression and eligible disks.
     *
     * @throws InvalidCronExpressionException If the cron expression is invalid.
     * @throws NoValidDisksException If no valid disks are found for the specified configurations.
     */
    public function forCleanup(CleanupSchedule $schedule): EligibleSchedule
    {
        $expression = $this->transformCronExpression($schedule->cron_expression);

        // Skip schedules with invalid cron expressions
        if (! $expression) {
            throw new InvalidCronExpressionException("Invalid cron expression for cleanup schedule '{$schedule->name}': '{$schedule->cron_expression}'");
        }

        $disks = $this->getDisksForJob($schedule->filesystemConfigurations);

        if ($schedule->filesystemConfigurations->isNotEmpty() && $disks === []) {
            throw new NoValidDisksException("No valid disks found for cleanup schedule '{$schedule->name}' with specified disks.");
        }

        return new EligibleSchedule($expression, $disks);
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
}
