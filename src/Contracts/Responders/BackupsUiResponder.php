<?php

namespace SameOldNick\BackupManager\Contracts\Responders;

use SameOldNick\BackupManager\DataTransferObjects\Responders\Backups\BackupsListViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Backups\PerformBackupViewData;

interface BackupsUiResponder
{
    /**
     * Renders the backups list screen.
     *
     * @return mixed
     */
    public function renderBackupsList(BackupsListViewData $data);

    /**
     * Renders the perform backup screen.
     *
     * @return mixed
     */
    public function renderPerformBackup(PerformBackupViewData $data);
}
