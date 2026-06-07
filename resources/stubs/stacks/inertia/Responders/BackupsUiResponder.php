<?php

namespace VendorName\BackupManager\Responders;

use Inertia\Inertia;
use SameOldNick\BackupManager\Broadcasting\Access\ChannelLease;
use SameOldNick\BackupManager\Contracts\Responders\BackupsUiResponder as BackupsUiResponderContract;
use SameOldNick\BackupManager\Models\Collections\BackupCollection;

class BackupsUiResponder implements BackupsUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderBackupsList(BackupCollection $backups)
    {
        return Inertia::render('dashboard/settings/backups/page', [
            'tab' => 'backups',
            'action' => 'list',
            'backups' => $backups->paginate(request()->query('per_page', 15))->withQueryString(),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderPerformBackup(string $type, string $uuid, ?ChannelLease $lease)
    {
        if (! $lease) {
            abort(404, __('backup::messages.backup_job_not_found'));
        }

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
