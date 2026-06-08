<?php

namespace VendorName\BackupManager\Responders;

use SameOldNick\BackupManager\Contracts\Responders\SchedulesUiResponder as SchedulesUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\SchedulesListViewData;

class SchedulesUiResponder implements SchedulesUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderSchedulesList(SchedulesListViewData $data)
    {
        //
    }
}
