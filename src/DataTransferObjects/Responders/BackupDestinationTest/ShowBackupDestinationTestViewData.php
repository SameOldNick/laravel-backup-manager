<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinationTest;

use SameOldNick\BackupManager\Broadcasting\Access\ChannelLease;
use SameOldNick\BackupManager\Contracts\FilesystemConfiguration;
use Spatie\Backup\Config\Config;

class ShowBackupDestinationTestViewData
{
    public function __construct(
        public readonly FilesystemConfiguration $configuration,
        public readonly string $uuid,
        public readonly ?ChannelLease $lease,
        public readonly Config $backupConfig,
    ) {
        //
    }
}
