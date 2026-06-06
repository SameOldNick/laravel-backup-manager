<?php

namespace VendorName\BackupManager\Responders;

use Inertia\Inertia;
use SameOldNick\BackupManager\Contracts\Responders\CleanupSchedulesUiResponder as CleanupSchedulesUiResponderContract;
use SameOldNick\BackupManager\Models\CleanupSchedule;

class CleanupSchedulesUiResponder implements CleanupSchedulesUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderCreateCleanupSchedule()
    {
        return Inertia::render('dashboard/settings/backups/page', [
            'tab' => 'schedule',
            'action' => 'create:cleanup',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderStoreCleanupSchedule(CleanupSchedule $schedule)
    {
        return redirect()->route('backup-manager.schedules.index');
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditCleanupSchedule(CleanupSchedule $schedule)
    {
        return Inertia::render('dashboard/settings/backups/page', [
            'tab' => 'schedule',
            'action' => 'edit:cleanup',
            'schedule' => $schedule,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateCleanupSchedule(CleanupSchedule $schedule)
    {
        return redirect()->route('backup-manager.schedules.index');
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyCleanupSchedule(CleanupSchedule $schedule)
    {
        return redirect()->route('backup-manager.schedules.index');
    }
}
