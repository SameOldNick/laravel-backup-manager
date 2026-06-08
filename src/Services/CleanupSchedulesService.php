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

    public function getCleanupSchedules(): CleanupScheduleCollection
    {
        return CleanupSchedule::all();
    }

    public function createCleanupSchedule(CreateCleanupScheduleData $data): CleanupSchedule
    {
        $schedule = CleanupSchedule::create([
            'name' => $data->name,
            'cron_expression' => $data->cronExpression,
            'is_active' => $data->isActive,
        ]);

        return $schedule;
    }

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

    public function removeCleanupSchedule(CleanupSchedule $schedule): void
    {
        $schedule->delete();
    }
}
