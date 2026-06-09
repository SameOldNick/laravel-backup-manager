<?php

namespace SameOldNick\BackupManager\Contracts\Responders;

use SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup\InitializeBackupViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup\PerformBackupViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup\StartBackupViewData;

interface PerformBackupUiResponder
{
    /**
     * Renders the initialize backup screen.
     *
     * @return mixed
     */
    public function renderInitializeBackup(InitializeBackupViewData $data);

    /**
     * Renders the start backup screen.
     *
     * @return mixed
     */
    public function renderStartBackup(StartBackupViewData $data);

    /**
     * Renders the perform backup screen.
     *
     * @return mixed
     */
    public function renderPerformBackup(PerformBackupViewData $data);
}
