<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations;

use SameOldNick\BackupManager\Models\Collections\FilesystemConfigurationCollection;

class BackupDestinationsListViewData
{
    public function __construct(
        public readonly FilesystemConfigurationCollection $backupDestinations,
    ) {
        //
    }
}
