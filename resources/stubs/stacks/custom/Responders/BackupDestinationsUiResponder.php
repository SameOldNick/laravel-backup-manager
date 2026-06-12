<?php

namespace VendorName\BackupManager\Responders;

use SameOldNick\BackupManager\Contracts\Responders\BackupDestinationsUiResponder as BackupDestinationsUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\BackupDestinationsListViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\DestroyBackupDestinationViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\EditBackupDestinationViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\StoreBackupDestinationViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\UpdateBackupDestinationViewData;

class BackupDestinationsUiResponder implements BackupDestinationsUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderBackupDestinationsList(BackupDestinationsListViewData $data)
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
    public function renderStoreBackupDestination(StoreBackupDestinationViewData $data)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditBackupDestination(EditBackupDestinationViewData $data)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateBackupDestination(UpdateBackupDestinationViewData $data)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyBackupDestination(DestroyBackupDestinationViewData $data)
    {
        //
    }
}
