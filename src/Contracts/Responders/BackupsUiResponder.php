<?php

namespace SameOldNick\BackupManager\Contracts\Responders;

use SameOldNick\BackupManager\DataTransferObjects\Responders\Backups\BackupsListViewData;

interface BackupsUiResponder
{
    /**
     * Renders the backups list screen.
     *
     * @return mixed
     */
    public function renderBackupsList(BackupsListViewData $data);
}
