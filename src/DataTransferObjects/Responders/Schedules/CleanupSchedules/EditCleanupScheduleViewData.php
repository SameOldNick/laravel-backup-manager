<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules;

use SameOldNick\BackupManager\Models\CleanupSchedule;
use SameOldNick\BackupManager\Models\Collections\FilesystemConfigurationCollection;

class EditCleanupScheduleViewData
{
    /**
     * @param  array<int, int>  $selectedDestinationIds  Ids of the destinations currently attached to the schedule
     */
    public function __construct(
        public readonly CleanupSchedule $schedule,
        public readonly FilesystemConfigurationCollection $configurations,
        public readonly array $selectedDestinationIds,
    ) {
        //
    }
}
