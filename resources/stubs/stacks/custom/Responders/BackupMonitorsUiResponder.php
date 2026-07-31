<?php

namespace VendorName\BackupManager\Responders;

use SameOldNick\BackupManager\Contracts\Responders\BackupMonitorsUiResponder as BackupMonitorsUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\BackupMonitorsListViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\DestroyBackupMonitorViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\EditBackupMonitorViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\StoreBackupMonitorViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\UpdateBackupMonitorViewData;

class BackupMonitorsUiResponder implements BackupMonitorsUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderBackupMonitorsList(BackupMonitorsListViewData $data)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderCreateBackupMonitor()
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderStoreBackupMonitor(StoreBackupMonitorViewData $data)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditBackupMonitor(EditBackupMonitorViewData $data)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateBackupMonitor(UpdateBackupMonitorViewData $data)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyBackupMonitor(DestroyBackupMonitorViewData $data)
    {
        //
    }
}
