<?php

namespace SameOldNick\BackupManager\Services;

use Illuminate\Support\Facades\DB;
use SameOldNick\BackupManager\DataTransferObjects\CreateBackupScheduleData;
use SameOldNick\BackupManager\DataTransferObjects\UpdateBackupScheduleData;
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

    public function getBackupSchedules(): BackupScheduleCollection
    {
        return BackupSchedule::all();
    }

    public function getAvailableDestinations(): FilesystemConfigurationCollection
    {
        return BackupSchedule::availableDestinations();
    }

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

    public function removeBackupSchedule(BackupSchedule $schedule): void
    {
        $schedule->delete();
    }
}
