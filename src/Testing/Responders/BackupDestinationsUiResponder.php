<?php

namespace SameOldNick\BackupManager\Testing\Responders;

use Illuminate\Pagination\AbstractPaginator;
use SameOldNick\BackupManager\Contracts\FilesystemConfiguration;
use SameOldNick\BackupManager\Contracts\Responders\BackupDestinationsUiResponder as BackupDestinationsUiResponderContract;
use Spatie\Backup\Config\Config;

class BackupDestinationsUiResponder implements BackupDestinationsUiResponderContract
{
    use Concerns\CreatesTestResponses;

    /**
     * {@inheritDoc}
     */
    public function renderBackupDestinationsList(AbstractPaginator $backupDestinations)
    {
        return $this->createTestResponse('list', [
            'backupDestinations' => $backupDestinations->toArray(),
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
