<?php

namespace VendorName\BackupManager\Responders;

use Illuminate\Pagination\AbstractPaginator;
use Inertia\Inertia;
use SameOldNick\BackupManager\Contracts\Responders\BackupsUiResponder as BackupsUiResponderContract;

class BackupsUiResponder implements BackupsUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderBackupsList(AbstractPaginator $backups)
    {
        return Inertia::render('dashboard/settings/backups/page', [
            'tab' => 'backups',
            'action' => 'list',
            'backups' => $backups,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderPerformBackup(string $type, string $uuid)
    {
        return Inertia::render('dashboard/settings/backups/page', [
            'tab' => 'backups',
            'action' => 'list',
            'performing_backup' => [
                'uuid' => $uuid,
                'type' => $type,
            ],
        ]);
    }
}
