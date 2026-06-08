<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules;

use SameOldNick\BackupManager\Models\CleanupSchedule;

class EditCleanupScheduleViewData
{
    public function __construct(
        public readonly CleanupSchedule $schedule,
    ) {
        //
    }
}
