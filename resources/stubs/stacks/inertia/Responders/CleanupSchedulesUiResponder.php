<?php

namespace VendorName\BackupManager\Responders;

use Inertia\Inertia;
use SameOldNick\BackupManager\Contracts\Responders\CleanupSchedulesUiResponder as CleanupSchedulesUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\DestroyCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\EditCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\StoreCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\UpdateCleanupScheduleViewData;

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
    public function renderStoreCleanupSchedule(StoreCleanupScheduleViewData $data)
    {
        return redirect()->route('backup-manager.schedules.index');
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditCleanupSchedule(EditCleanupScheduleViewData $data)
    {
        return Inertia::render('dashboard/settings/backups/page', [
            'tab' => 'schedule',
            'action' => 'edit:cleanup',
            'schedule' => $data->schedule,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateCleanupSchedule(UpdateCleanupScheduleViewData $data)
    {
        return redirect()->route('backup-manager.schedules.index');
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyCleanupSchedule(DestroyCleanupScheduleViewData $data)
    {
        return redirect()->route('backup-manager.schedules.index');
    }
}
