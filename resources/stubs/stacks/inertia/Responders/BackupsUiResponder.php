<?php

namespace VendorName\BackupManager\Responders;

use Inertia\Inertia;
use SameOldNick\BackupManager\Contracts\Responders\BackupsUiResponder as BackupsUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Backups\BackupsListViewData;

class BackupsUiResponder implements BackupsUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderBackupsList(BackupsListViewData $data)
    {
        return Inertia::render('dashboard/settings/backups/page', [
            'tab' => 'backups',
            'action' => 'list',
            'backups' => $data->backups,
        ]);
    }
}
