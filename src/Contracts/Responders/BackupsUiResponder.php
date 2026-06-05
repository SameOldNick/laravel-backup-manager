<?php

namespace SameOldNick\BackupManager\Contracts\Responders;

use Illuminate\Pagination\AbstractPaginator;

interface BackupsUiResponder
{
    /**
     * Renders the backups list screen.
     *
     * @return mixed
     */
    public function renderBackupsList(AbstractPaginator $backups);

    /**
     * Renders the perform backup screen.
     *
     * @return mixed
     */
    public function renderPerformBackup(string $type, string $uuid);
}
