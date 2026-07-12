<?php

namespace SameOldNick\BackupManager\Contracts\Responders;

use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\BackupMonitorsListViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\DestroyBackupMonitorViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\EditBackupMonitorViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\StoreBackupMonitorViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\UpdateBackupMonitorViewData;

interface BackupMonitorsUiResponder
{
    /**
     * Renders the backup monitors list screen.
     *
     * @return mixed
     */
    public function renderBackupMonitorsList(BackupMonitorsListViewData $data);

    /**
     * Renders the create backup monitor screen.
     *
     * @return mixed
     */
    public function renderCreateBackupMonitor();

    /**
     * Renders the response after storing a backup monitor.
     *
     * @return mixed
     */
    public function renderStoreBackupMonitor(StoreBackupMonitorViewData $data);

    /**
     * Renders the edit backup monitor screen.
     *
     * @return mixed
     */
    public function renderEditBackupMonitor(EditBackupMonitorViewData $data);

    /**
     * Renders the response after updating a backup monitor.
     *
     * @return mixed
     */
    public function renderUpdateBackupMonitor(UpdateBackupMonitorViewData $data);

    /**
     * Renders the response after deleting a backup monitor.
     *
     * @return mixed
     */
    public function renderDestroyBackupMonitor(DestroyBackupMonitorViewData $data);
}