<?php

namespace SameOldNick\BackupManager\Testing\Responders;

use SameOldNick\BackupManager\Contracts\Responders\BackupDestinationsUiResponder as BackupDestinationsUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\BackupDestinationsListViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\DestroyBackupDestinationViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\EditBackupDestinationViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\StoreBackupDestinationViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\UpdateBackupDestinationViewData;
use SameOldNick\BackupManager\Testing\Concerns;

class BackupDestinationsUiResponder implements BackupDestinationsUiResponderContract
{
    use Concerns\CreatesTestResponses;

    /**
     * {@inheritDoc}
     */
    public function renderBackupDestinationsList(BackupDestinationsListViewData $data)
    {
        return $this->createTestResponse('list', [
            'backupDestinations' => $data->backupDestinations->paginate()->toArray(),
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
    public function renderStoreBackupDestination(StoreBackupDestinationViewData $data)
    {
        return $this->createTestResponse('store', [
            'configuration' => $data->configuration,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditBackupDestination(EditBackupDestinationViewData $data)
    {
        return $this->createTestResponse('edit', [
            'backupConfig' => $data->backupConfig,
            'configuration' => $data->configuration,
            'enabled' => $data->configuration->isEnabled($data->backupConfig),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateBackupDestination(UpdateBackupDestinationViewData $data)
    {
        return $this->createTestResponse('update', [
            'destination' => $data->destination,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyBackupDestination(DestroyBackupDestinationViewData $data)
    {
        return $this->createTestResponse('destroy', [
            'destination' => $data->destination,
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
