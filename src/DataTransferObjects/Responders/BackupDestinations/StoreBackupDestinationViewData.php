<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations;

use SameOldNick\BackupManager\Contracts\FilesystemConfiguration;

class StoreBackupDestinationViewData
{
    public function __construct(
        public readonly FilesystemConfiguration $configuration,
    ) {
        //
    }
}
