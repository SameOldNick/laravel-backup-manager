<?php

namespace VendorName\BackupManager\Responders;

use Inertia\Inertia;
use SameOldNick\BackupManager\Contracts\Responders\CleanupSchedulesUiResponder as CleanupSchedulesUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\CreateCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\DestroyCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\EditCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\StoreCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\UpdateCleanupScheduleViewData;

class CleanupSchedulesUiResponder implements CleanupSchedulesUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderCreateCleanupSchedule(CreateCleanupScheduleViewData $data)
    {
        return Inertia::render('dashboard/settings/backups/page', [
            'tab' => 'schedule',
            'action' => 'create:cleanup',
            'destinations' => $data->configurations,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderStoreCleanupSchedule(StoreCleanupScheduleViewData $data)
    {
        return redirect()
            ->route('backup-manager.schedules.index')
            ->with('success', __('backup-manager::messages.cleanup_schedule_created'));
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditCleanupSchedule(EditCleanupScheduleViewData $data)
    {
        $selectedDestinationIds = $data->configurations
            ->filter(fn ($destination) => $destination->is_active)
            ->pluck('id')
            ->all();

        return Inertia::render('dashboard/settings/backups/page', [
            'tab' => 'schedule',
            'action' => 'edit:cleanup',
            'schedule' => $data->schedule,
            'destination_ids' => $selectedDestinationIds,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateCleanupSchedule(UpdateCleanupScheduleViewData $data)
    {
        return redirect()
            ->route('backup-manager.schedules.index')
            ->with('success', __('backup-manager::messages.cleanup_schedule_updated'));
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyCleanupSchedule(DestroyCleanupScheduleViewData $data)
    {
        return redirect()
            ->route('backup-manager.schedules.index')
            ->with('success', __('backup-manager::messages.cleanup_schedule_deleted'));
    }
}
