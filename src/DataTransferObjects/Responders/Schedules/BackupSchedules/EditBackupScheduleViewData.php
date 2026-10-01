<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules;

use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Models\Collections\FilesystemConfigurationCollection;

class EditBackupScheduleViewData
{
    /**
     * @param  array<int, int>  $selectedDestinationIds  Ids of the destinations currently attached to the schedule
     */
    public function __construct(
        public readonly BackupSchedule $schedule,
        public readonly FilesystemConfigurationCollection $configurations,
        public readonly array $selectedDestinationIds,
    ) {
        //
    }
}
