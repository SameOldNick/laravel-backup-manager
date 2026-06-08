<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations;

use SameOldNick\BackupManager\Contracts\FilesystemConfiguration;

class UpdateBackupDestinationViewData
{
    public function __construct(
        public readonly FilesystemConfiguration $destination,
    ) {
        //
    }
}
