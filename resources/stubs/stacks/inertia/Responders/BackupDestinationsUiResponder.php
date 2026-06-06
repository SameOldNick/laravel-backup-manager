<?php

namespace VendorName\BackupManager\Responders;

use Illuminate\Pagination\AbstractPaginator;
use SameOldNick\BackupManager\Contracts\FilesystemConfiguration;
use SameOldNick\BackupManager\Contracts\Responders\BackupDestinationsUiResponder as BackupDestinationsUiResponderContract;
use Spatie\Backup\Config\Config;

class BackupDestinationsUiResponder implements BackupDestinationsUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderBackupDestinationsList(AbstractPaginator $backupDestinations)
    {
        return inertia('dashboard/settings/backups/page', [
            'tab' => 'destinations',
            'action' => 'list',
            'destinations' => $backupDestinations,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderCreateBackupDestination()
    {
        return inertia('dashboard/settings/backups/page', [
            'tab' => 'destinations',
            'action' => 'create',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderStoreBackupDestination(FilesystemConfiguration $configuration)
    {
        return redirect()->route('backup.destinations.show', ['destination' => $configuration]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditBackupDestination(Config $backupConfig, FilesystemConfiguration $configuration, bool $enabled)
    {
        return inertia('dashboard/settings/backups/page', [
            'tab' => 'destinations',
            'action' => 'edit',
            'destination' => $configuration,
            'enabled' => $enabled,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateBackupDestination(FilesystemConfiguration $destination)
    {
        return back();
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyBackupDestination(FilesystemConfiguration $destination)
    {
        return back();
    }
}
