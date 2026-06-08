<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules;

use SameOldNick\BackupManager\Models\BackupSchedule;

class StoreBackupScheduleViewData
{
    public function __construct(
        public readonly BackupSchedule $schedule,
    ) {
        //
    }
}
