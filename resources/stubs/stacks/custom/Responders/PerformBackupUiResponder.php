<?php

namespace VendorName\BackupManager\Responders;

use SameOldNick\BackupManager\Contracts\Responders\PerformBackupUiResponder as PerformBackupResponderUiContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup\InitializeBackupViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup\PerformBackupViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup\StartBackupViewData;

class PerformBackupUiResponder implements PerformBackupResponderUiContract
{
    /**
     * {@inheritDoc}
     */
    public function renderInitializeBackup(InitializeBackupViewData $data)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderStartBackup(StartBackupViewData $data)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderPerformBackup(PerformBackupViewData $data)
    {
        //
    }
}
