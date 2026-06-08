<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules;

use SameOldNick\BackupManager\Models\BackupSchedule;

class DestroyBackupScheduleViewData
{
    public function __construct(
        public readonly BackupSchedule $schedule,
    ) {
        //
    }
}
