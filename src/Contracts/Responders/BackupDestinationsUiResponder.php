<?php

namespace SameOldNick\BackupManager\Contracts\Responders;

use Illuminate\Pagination\AbstractPaginator;
use SameOldNick\BackupManager\Contracts\FilesystemConfiguration;
use Spatie\Backup\Config\Config;

interface BackupDestinationsUiResponder
{
    /**
     * Renders the backup destinations list screen.
     *
     * @return mixed
     */
    public function renderBackupDestinationsList(AbstractPaginator $backupDestinations);

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
    public function renderStoreBackupDestination(FilesystemConfiguration $configuration);

    /**
     * Renders the edit backup destination screen.
     *
     * @return mixed
     */
    public function renderEditBackupDestination(Config $backupConfig, FilesystemConfiguration $configuration, bool $enabled);

    /**
     * Renders the response after updating a backup destination.
     *
     * @return mixed
     */
    public function renderUpdateBackupDestination(FilesystemConfiguration $destination);

    /**
     * Renders the response after deleting a backup destination.
     *
     * @return mixed
     */
    public function renderDestroyBackupDestination(FilesystemConfiguration $destination);
}
