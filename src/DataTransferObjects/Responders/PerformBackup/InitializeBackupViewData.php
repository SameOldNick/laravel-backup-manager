<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup;

use SameOldNick\BackupManager\Broadcasting\Access\ChannelLease;
use SameOldNick\BackupManager\Enums\BackupTypes;

class InitializeBackupViewData
{
    public function __construct(
        public readonly BackupTypes $type,
        public readonly string $uuid,
        public readonly ChannelLease $lease,
    ) {
        //
    }
}
