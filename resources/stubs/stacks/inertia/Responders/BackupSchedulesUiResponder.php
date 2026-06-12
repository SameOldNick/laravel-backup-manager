<?php

namespace VendorName\BackupManager\Responders;

use Inertia\Inertia;
use SameOldNick\BackupManager\Contracts\Responders\BackupSchedulesUiResponder as BackupSchedulesUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\CreateBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\DestroyBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\EditBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\StoreBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\UpdateBackupScheduleViewData;

class BackupSchedulesUiResponder implements BackupSchedulesUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderCreateBackupSchedule(CreateBackupScheduleViewData $data)
    {
        return Inertia::render('dashboard/settings/backups/page', [
            'tab' => 'schedule',
            'action' => 'create:backup',
            'destinations' => $data->configurations,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderStoreBackupSchedule(StoreBackupScheduleViewData $data)
    {
        return redirect()
            ->route('backup-manager.schedules.index')
            ->with('success', __('backup::messages.backup_schedule_created'));
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditBackupSchedule(EditBackupScheduleViewData $data)
    {
        $selectedDestinationIds = $data->schedule
            ->filesystemConfigurations()
            ->pluck('filesystem_configurations.id')
            ->all();

        return Inertia::render('dashboard/settings/backups/page', [
            'tab' => 'schedule',
            'action' => 'edit:backup',
            'schedule' => [
                ...$data->schedule->toArray(),
                'destination_ids' => $selectedDestinationIds,
            ],
            'destinations' => $data->configurations,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateBackupSchedule(UpdateBackupScheduleViewData $data)
    {
        return redirect()
            ->route('backup-manager.schedules.index')
            ->with('success', __('backup::messages.backup_schedule_updated'));
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyBackupSchedule(DestroyBackupScheduleViewData $data)
    {
        return redirect()
            ->route('backup-manager.schedules.index')
            ->with('success', __('backup::messages.backup_schedule_deleted'));
    }
}
