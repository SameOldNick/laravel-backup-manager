<?php

namespace VendorName\BackupManager\Responders;

use Inertia\Inertia;
use SameOldNick\BackupManager\Contracts\Responders\PerformBackupUiResponder as PerformBackupUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup\InitializeBackupViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup\PerformBackupViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup\StartBackupViewData;

class PerformBackupUiResponder implements PerformBackupUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderInitializeBackup(InitializeBackupViewData $data)
    {
        return redirect()->temporarySignedRoute('backup.perform.show', $data->lease->expiresAt, [
            'type' => $data->type,
            'uuid' => $data->uuid,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderStartBackup(StartBackupViewData $data)
    {
        return [
            'message' => __('backup::messages.backup_started'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function renderPerformBackup(PerformBackupViewData $data)
    {
        if (! $data->lease) {
            abort(404, __('backup::messages.backup_job_not_found'));
        }

        $startUrl = url()->temporarySignedRoute('backup.perform.start', $data->lease->expiresAt, [
            'type' => $data->type,
            'uuid' => $data->uuid,
        ]);

        return Inertia::render('dashboard/settings/backups/page', [
            'tab' => 'backups',
            'action' => 'list',
            'performing_backup' => [
                'uuid' => $data->uuid,
                'type' => $data->type,
                'start_url' => $startUrl,
            ],
        ]);
    }
}
