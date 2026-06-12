<?php

namespace SameOldNick\BackupManager\Services;

use Illuminate\Support\Facades\DB;
use SameOldNick\BackupManager\DataTransferObjects\Services\CreateBackupScheduleData;
use SameOldNick\BackupManager\DataTransferObjects\Services\UpdateBackupScheduleData;
use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Models\Collections\BackupScheduleCollection;
use SameOldNick\BackupManager\Models\Collections\FilesystemConfigurationCollection;

class BackupSchedulesService
{
    /**
     * Initializes BackupSchedulesService instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Retrieves a collection of backup schedules.
     *
     * @return BackupScheduleCollection A collection of BackupSchedule models representing the backup schedules
     */
    public function getBackupSchedules(): BackupScheduleCollection
    {
        return BackupSchedule::all();
    }

    /**
     * Retrieves a collection of available backup destinations that can be associated with backup schedules.
     *
     * @return FilesystemConfigurationCollection A collection of FilesystemConfiguration models representing the available backup destinations
     */
    public function getAvailableDestinations(): FilesystemConfigurationCollection
    {
        return BackupSchedule::availableDestinations();
    }

    /**
     * Creates a new backup schedule based on the provided data.
     *
     * @param  CreateBackupScheduleData  $data  The data for creating the backup schedule
     * @return BackupSchedule The created backup schedule
     */
    public function createBackupSchedule(CreateBackupScheduleData $data): BackupSchedule
    {
        $schedule = BackupSchedule::create([
            'name' => $data->name,
            'type' => $data->type,
            'cron_expression' => $data->cronExpression,
            'is_active' => $data->isActive,
        ]);

        if ($data->destinationIds !== null) {
            $schedule->filesystemConfigurations()->sync($data->destinationIds);
        }

        return $schedule;
    }

    /**
     * Updates an existing backup schedule with the provided data.
     *
     * @param  BackupSchedule  $schedule  The backup schedule to update
     * @param  UpdateBackupScheduleData  $data  The data for updating the backup schedule
     * @return BackupSchedule The updated backup schedule
     */
    public function updateBackupSchedule(BackupSchedule $schedule, UpdateBackupScheduleData $data): BackupSchedule
    {
        return DB::transaction(function () use ($schedule, $data) {
            if ($data->name !== null) {
                $schedule->name = $data->name;
            }

            if ($data->type !== null) {
                $schedule->type = $data->type;
            }

            if ($data->cronExpression !== null) {
                $schedule->cron_expression = $data->cronExpression;
            }

            if ($data->isActive !== null) {
                $schedule->is_active = (bool) $data->isActive;
            }

            if ($schedule->isDirty()) {
                $schedule->save();
            }

            if ($data->destinationIds !== null) {
                $schedule->filesystemConfigurations()->sync($data->destinationIds);
            }

            return $schedule;
        });
    }

    /**
     * Removes a backup schedule.
     *
     * @param  BackupSchedule  $schedule  The backup schedule to remove
     */
    public function removeBackupSchedule(BackupSchedule $schedule): void
    {
        $schedule->delete();
    }
}
