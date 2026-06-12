<?php

namespace SameOldNick\BackupManager\Services;

use SameOldNick\BackupManager\DataTransferObjects\Services\CreateCleanupScheduleData;
use SameOldNick\BackupManager\DataTransferObjects\Services\UpdateCleanupScheduleData;
use SameOldNick\BackupManager\Models\CleanupSchedule;
use SameOldNick\BackupManager\Models\Collections\CleanupScheduleCollection;

class CleanupSchedulesService
{
    /**
     * Initializes CleanupSchedulesService instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Retrieves a collection of cleanup schedules.
     *
     * @return CleanupScheduleCollection A collection of CleanupSchedule models representing the cleanup schedules
     */
    public function getCleanupSchedules(): CleanupScheduleCollection
    {
        return CleanupSchedule::all();
    }

    /**
     * Creates a new cleanup schedule based on the provided data.
     *
     * @param  CreateCleanupScheduleData  $data  The data for creating the cleanup schedule
     * @return CleanupSchedule The created cleanup schedule
     */
    public function createCleanupSchedule(CreateCleanupScheduleData $data): CleanupSchedule
    {
        $schedule = CleanupSchedule::create([
            'name' => $data->name,
            'cron_expression' => $data->cronExpression,
            'is_active' => $data->isActive,
        ]);

        return $schedule;
    }

    /**
     * Updates an existing cleanup schedule with the provided data.
     *
     * @param  CleanupSchedule  $schedule  The cleanup schedule to update
     * @param  UpdateCleanupScheduleData  $data  The data for updating the cleanup schedule
     * @return CleanupSchedule The updated cleanup schedule
     */
    public function updateCleanupSchedule(CleanupSchedule $schedule, UpdateCleanupScheduleData $data): CleanupSchedule
    {
        if ($data->name !== null) {
            $schedule->name = $data->name;
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

        return $schedule;
    }

    /**
     * Removes a cleanup schedule.
     *
     * @param  CleanupSchedule  $schedule  The cleanup schedule to remove
     */
    public function removeCleanupSchedule(CleanupSchedule $schedule): void
    {
        $schedule->delete();
    }
}
