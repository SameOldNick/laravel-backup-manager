<?php

namespace VendorName\BackupManager\Responders;

use Illuminate\Database\Eloquent\Collection;
use Inertia\Inertia;
use SameOldNick\BackupManager\Contracts\Responders\SchedulesUiResponder as SchedulesUiResponderContract;

class SchedulesUiResponder implements SchedulesUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderSchedulesList(Collection $backupSchedules, Collection $cleanupSchedules)
    {
        return Inertia::render('dashboard/settings/backups/page', [
            'tab' => 'schedule',
            'action' => 'list',
            'backupSchedules' => $backupSchedules,
            'cleanupSchedules' => $cleanupSchedules,
        ]);
    }
}
