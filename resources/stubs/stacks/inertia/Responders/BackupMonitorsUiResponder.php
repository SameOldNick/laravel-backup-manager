<?php

namespace VendorName\BackupManager\Responders;

use Inertia\Inertia;
use SameOldNick\BackupManager\Contracts\Responders\BackupMonitorsUiResponder as BackupMonitorsUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\BackupMonitorsListViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\DestroyBackupMonitorViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\EditBackupMonitorViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\StoreBackupMonitorViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\UpdateBackupMonitorViewData;
use SameOldNick\BackupManager\Services\BackupDestinationsService;

class BackupMonitorsUiResponder implements BackupMonitorsUiResponderContract
{
    public function __construct(
        protected readonly BackupDestinationsService $backupDestinationsService
    ) {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderBackupMonitorsList(BackupMonitorsListViewData $data)
    {
        $request = request();

        return Inertia::render('backup-manager/monitors', [
            'action' => 'list',
            'monitors' => $data->backupMonitors->load('filesystemConfigurations'),
            'filters' => [
                'active' => $request->has('active') ? $request->boolean('active') : null,
                'query' => $request->query('query', ''),
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderCreateBackupMonitor()
    {
        return Inertia::render('backup-manager/monitors', [
            'action' => 'create',
            'destinations' => $this->backupDestinationsService->getBackupDestinations(),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderStoreBackupMonitor(StoreBackupMonitorViewData $data)
    {
        return redirect()
            ->route('backup.monitors.edit', ['monitor' => $data->backupMonitor])
            ->with('success', __('backup-manager::messages.monitor_created'));
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditBackupMonitor(EditBackupMonitorViewData $data)
    {
        return Inertia::render('backup-manager/monitors', [
            'action' => 'edit',
            'destinations' => $this->backupDestinationsService->getBackupDestinations(),
            'monitor' => $data->backupMonitor->load('filesystemConfigurations'),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateBackupMonitor(UpdateBackupMonitorViewData $data)
    {
        return back()
            ->with('success', __('backup-manager::messages.monitor_updated'));
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyBackupMonitor(DestroyBackupMonitorViewData $data)
    {
        return back()
            ->with('success', __('backup-manager::messages.monitor_deleted'));
    }
}
