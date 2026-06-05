<?php

namespace SameOldNick\BackupManager\Contracts\Responders;

use Illuminate\Database\Eloquent\Collection;

interface SchedulesUiResponder
{
    /**
     * Renders the schedules list screen.
     *
     * @return mixed
     */
    public function renderSchedulesList(Collection $backupSchedules, Collection $cleanupSchedules);
}
