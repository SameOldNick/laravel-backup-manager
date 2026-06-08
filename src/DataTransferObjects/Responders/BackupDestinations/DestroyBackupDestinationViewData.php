<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations;

use SameOldNick\BackupManager\Contracts\FilesystemConfiguration;

class DestroyBackupDestinationViewData
{
    public function __construct(
        public readonly FilesystemConfiguration $destination,
    ) {
        //
    }
}
