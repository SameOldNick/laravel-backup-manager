<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules;

use SameOldNick\BackupManager\Models\BackupSchedule;

class UpdateBackupScheduleViewData
{
    public function __construct(
        public readonly BackupSchedule $schedule,
    ) {
        //
    }
}
