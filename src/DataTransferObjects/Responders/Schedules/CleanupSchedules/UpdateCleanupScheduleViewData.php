<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules;

use SameOldNick\BackupManager\Models\CleanupSchedule;

class UpdateCleanupScheduleViewData
{
    public function __construct(
        public readonly CleanupSchedule $schedule,
    ) {
        //
    }
}
