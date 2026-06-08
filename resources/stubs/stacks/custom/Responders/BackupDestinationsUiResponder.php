<?php

namespace VendorName\BackupManager\Responders;

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
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderCreateBackupDestination()
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderStoreBackupDestination(FilesystemConfiguration $configuration)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditBackupDestination(Config $backupConfig, FilesystemConfiguration $configuration)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderBackupDestinationTestResult(Config $backupConfig, FilesystemConfiguration $configuration, string $uuid)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateBackupDestination(FilesystemConfiguration $destination)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyBackupDestination(FilesystemConfiguration $destination)
    {
        //
    }
}
