<?php

namespace SameOldNick\BackupManager\Contracts\Responders;

use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\SchedulesListViewData;

interface SchedulesUiResponder
{
    /**
     * Renders the schedules list screen.
     *
     * @return mixed
     */
    public function renderSchedulesList(SchedulesListViewData $data);
}
