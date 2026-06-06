<?php

namespace VendorName\BackupManager\Responders;

use Illuminate\Database\Eloquent\Collection;
use SameOldNick\BackupManager\Contracts\Responders\SchedulesUiResponder as SchedulesUiResponderContract;

class SchedulesUiResponder implements SchedulesUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderSchedulesList(Collection $backupSchedules, Collection $cleanupSchedules)
    {
        //
    }
}
