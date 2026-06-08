<?php

namespace SameOldNick\BackupManager\Contracts\Responders;

use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\BackupDestinationsListViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\BackupDestinationTestResultViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\DestroyBackupDestinationViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\EditBackupDestinationViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\StoreBackupDestinationViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\UpdateBackupDestinationViewData;

interface BackupDestinationsUiResponder
{
    /**
     * Renders the backup destinations list screen.
     *
     * @return mixed
     */
    public function renderBackupDestinationsList(BackupDestinationsListViewData $data);

    /**
     * Renders the create backup destination screen.
     *
     * @return mixed
     */
    public function renderCreateBackupDestination();

    /**
     * Renders the response after storing a backup destination.
     *
     * @return mixed
     */
    public function renderStoreBackupDestination(StoreBackupDestinationViewData $data);

    /**
     * Renders the edit backup destination screen.
     *
     * @return mixed
     */
    public function renderEditBackupDestination(EditBackupDestinationViewData $data);

    /**
     * Renders the response after testing a backup destination.
     *
     * @return mixed
     */
    public function renderBackupDestinationTestResult(BackupDestinationTestResultViewData $data);

    /**
     * Renders the response after updating a backup destination.
     *
     * @return mixed
     */
    public function renderUpdateBackupDestination(UpdateBackupDestinationViewData $data);

    /**
     * Renders the response after deleting a backup destination.
     *
     * @return mixed
     */
    public function renderDestroyBackupDestination(DestroyBackupDestinationViewData $data);
}
