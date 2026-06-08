<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules;

use Illuminate\Database\Eloquent\Collection;
use SameOldNick\BackupManager\Models\Collections\BackupScheduleCollection;

class SchedulesListViewData
{
    public function __construct(
        public readonly BackupScheduleCollection $backupSchedules,
        public readonly Collection $cleanupSchedules,
    ) {
        //
    }
}
