<?php

namespace SameOldNick\BackupManager\Testing\Responders;

use SameOldNick\BackupManager\Contracts\Responders\BackupMonitorsUiResponder as BackupMonitorsUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\BackupMonitorsListViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\DestroyBackupMonitorViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\EditBackupMonitorViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\StoreBackupMonitorViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\UpdateBackupMonitorViewData;
use SameOldNick\BackupManager\Testing\Concerns;

class BackupMonitorsUiResponder implements BackupMonitorsUiResponderContract
{
    use Concerns\CreatesTestResponses;

    public function renderBackupMonitorsList(BackupMonitorsListViewData $data)
    {
        return $this->createTestResponse('list', [
            'backupMonitors' => $data->backupMonitors->paginate()->toArray(),
        ]);
    }

    public function renderCreateBackupMonitor()
    {
        return $this->createTestResponse('create', []);
    }

    public function renderStoreBackupMonitor(StoreBackupMonitorViewData $data)
    {
        return $this->createTestResponse('store', [
            'backupMonitor' => $data->backupMonitor->toArray(),
        ]);
    }

    public function renderEditBackupMonitor(EditBackupMonitorViewData $data)
    {
        return $this->createTestResponse('edit', [
            'backupMonitor' => $data->backupMonitor->toArray(),
        ]);
    }

    public function renderUpdateBackupMonitor(UpdateBackupMonitorViewData $data)
    {
        return $this->createTestResponse('update', [
            'backupMonitor' => $data->backupMonitor->toArray(),
        ]);
    }

    public function renderDestroyBackupMonitor(DestroyBackupMonitorViewData $data)
    {
        return $this->createTestResponse('destroy', [
            'id' => $data->id,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function getSourceResponder(): string
    {
        return 'backup-monitors';
    }
}
