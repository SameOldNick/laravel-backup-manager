<?php

namespace VendorName\BackupManager\Responders;

use Inertia\Inertia;
use SameOldNick\BackupManager\Contracts\FilesystemConfiguration;
use SameOldNick\BackupManager\Contracts\Responders\BackupDestinationsUiResponder as BackupDestinationsUiResponderContract;
use SameOldNick\BackupManager\Models\Collections\FilesystemConfigurationCollection;
use Spatie\Backup\Config\Config;

class BackupDestinationsUiResponder implements BackupDestinationsUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderBackupDestinationsList(FilesystemConfigurationCollection $backupDestinations)
    {
        return Inertia::render('dashboard/settings/backups/page', [
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
    public function renderEditBackupDestination(Config $backupConfig, FilesystemConfiguration $configuration)
    {
        return inertia('dashboard/settings/backups/page', [
            'tab' => 'destinations',
            'action' => 'edit',
            'destination' => $configuration,
            'enabled' => $configuration->isEnabled($backupConfig),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderBackupDestinationTestResult(Config $backupConfig, FilesystemConfiguration $configuration, string $uuid)
    {
        return inertia('dashboard/settings/backups/page', [
            'tab' => 'destinations',
            'action' => 'edit',
            'destination' => $configuration,
            'enabled' => $configuration->isEnabled($backupConfig),
            'testUuid' => $uuid,
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
