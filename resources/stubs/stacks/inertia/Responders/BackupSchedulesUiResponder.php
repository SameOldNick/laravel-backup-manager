<?php

namespace VendorName\BackupManager\Responders;

use Illuminate\Database\Eloquent\Collection;
use Inertia\Inertia;
use SameOldNick\BackupManager\Contracts\Responders\BackupSchedulesUiResponder as BackupSchedulesUiResponderContract;
use SameOldNick\BackupManager\Models\BackupSchedule;

class BackupSchedulesUiResponder implements BackupSchedulesUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderCreateBackupSchedule(Collection $configurations)
    {
        return Inertia::render('dashboard/settings/backups/page', [
            'tab' => 'schedule',
            'action' => 'create:backup',
            'destinations' => $configurations,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderStoreBackupSchedule(BackupSchedule $schedule)
    {
        return redirect()->route('backup-manager.schedules.index');
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditBackupSchedule(BackupSchedule $schedule, Collection $destinations)
    {
        $selectedDestinationIds = $schedule
            ->filesystemConfigurations()
            ->pluck('filesystem_configurations.id')
            ->all();

        return Inertia::render('dashboard/settings/backups/page', [
            'tab' => 'schedule',
            'action' => 'edit:backup',
            'schedule' => [
                ...$schedule->toArray(),
                'destination_ids' => $selectedDestinationIds,
            ],
            'destinations' => $destinations,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateBackupSchedule(BackupSchedule $schedule)
    {
        return redirect()->route('backup-manager.schedules.index');
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyBackupSchedule(BackupSchedule $schedule)
    {
        return redirect()->route('backup-manager.schedules.index');
    }
}
