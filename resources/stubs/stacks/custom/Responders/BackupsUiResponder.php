<?php

namespace VendorName\BackupManager\Responders;

use SameOldNick\BackupManager\Contracts\Responders\BackupsUiResponder as BackupsUiResponderContract;
use SameOldNick\BackupManager\Models\Collections\BackupCollection;

class BackupsUiResponder implements BackupsUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderBackupsList(BackupCollection $backups)
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
