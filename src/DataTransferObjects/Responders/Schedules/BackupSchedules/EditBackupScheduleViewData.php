<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules;

use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Models\Collections\FilesystemConfigurationCollection;

class EditBackupScheduleViewData
{
    public function __construct(
        public readonly BackupSchedule $schedule,
        public readonly FilesystemConfigurationCollection $configurations,
    ) {
        //
    }
}
