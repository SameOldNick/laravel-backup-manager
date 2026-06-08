<?php

namespace SameOldNick\BackupManager\Contracts\Responders;

use Illuminate\Database\Eloquent\Collection;
use SameOldNick\BackupManager\Models\Collections\BackupScheduleCollection;

interface SchedulesUiResponder
{
    /**
     * Renders the schedules list screen.
     *
     * @return mixed
     */
    public function renderSchedulesList(BackupScheduleCollection $backupSchedules, Collection $cleanupSchedules);
}
