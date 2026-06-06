<?php

namespace VendorName\BackupManager\Responders;

use Illuminate\Pagination\AbstractPaginator;
use SameOldNick\BackupManager\Contracts\Responders\BackupsUiResponder as BackupsUiResponderContract;

class BackupsUiResponder implements BackupsUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderBackupsList(AbstractPaginator $backups)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderPerformBackup(string $type, string $uuid)
    {
        //
    }
}
