<?php

namespace SameOldNick\BackupManager\Contracts\Responders;

use SameOldNick\BackupManager\Models\Collections\BackupCollection;

interface BackupUiResponder
{
    /**
     * Renders the backups list screen.
     *
     * @return mixed
     */
    public function renderBackupsList(BackupCollection $backups);

    /**
     * Renders the perform backup screen.
     *
     * @return mixed
     */
    public function renderPerformBackup(string $type, string $uuid);
}
