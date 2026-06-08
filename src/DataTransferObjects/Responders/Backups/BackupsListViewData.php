<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\Backups;

use SameOldNick\BackupManager\Models\Collections\BackupCollection;

class BackupsListViewData
{
    public function __construct(
        public readonly BackupCollection $backups,
    ) {
        //
    }
}
