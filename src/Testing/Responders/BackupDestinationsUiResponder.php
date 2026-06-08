<?php

namespace SameOldNick\BackupManager\Testing\Responders;

use SameOldNick\BackupManager\Contracts\FilesystemConfiguration;
use SameOldNick\BackupManager\Contracts\Responders\BackupDestinationsUiResponder as BackupDestinationsUiResponderContract;
use SameOldNick\BackupManager\Models\Collections\FilesystemConfigurationCollection;
use SameOldNick\BackupManager\Testing\Concerns;
use Spatie\Backup\Config\Config;

class BackupDestinationsUiResponder implements BackupDestinationsUiResponderContract
{
    use Concerns\CreatesTestResponses;

    /**
     * {@inheritDoc}
     */
    public function renderBackupDestinationsList(FilesystemConfigurationCollection $backupDestinations)
    {
        return $this->createTestResponse('list', [
            'backupDestinations' => $backupDestinations->paginate()->toArray(),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderCreateBackupDestination()
    {
        return $this->createTestResponse('create');
    }

    /**
     * {@inheritDoc}
     */
    public function renderStoreBackupDestination(FilesystemConfiguration $configuration)
    {
        return $this->createTestResponse('store', [
            'configuration' => $configuration,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditBackupDestination(Config $backupConfig, FilesystemConfiguration $configuration, bool $enabled)
    {
        return $this->createTestResponse('edit', [
            'backupConfig' => $backupConfig,
            'configuration' => $configuration,
            'enabled' => $enabled,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateBackupDestination(FilesystemConfiguration $destination)
    {
        return $this->createTestResponse('update', [
            'destination' => $destination,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyBackupDestination(FilesystemConfiguration $destination)
    {
        return $this->createTestResponse('destroy', [
            'destination' => $destination,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function getSourceResponder(): string
    {
        return 'backup-destinations';
    }
}
