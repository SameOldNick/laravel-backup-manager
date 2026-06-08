<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations;

use SameOldNick\BackupManager\Contracts\FilesystemConfiguration;
use Spatie\Backup\Config\Config;

class BackupDestinationTestResultViewData
{
    public function __construct(
        public readonly Config $backupConfig,
        public readonly FilesystemConfiguration $configuration,
        public readonly string $uuid,
    ) {
        //
    }
}
