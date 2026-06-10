<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup;

use SameOldNick\BackupManager\Broadcasting\Access\ChannelLease;
use SameOldNick\BackupManager\Models\BackupRun;

class StartBackupViewData
{
    public function __construct(
        public readonly string $type,
        public readonly string $uuid,
        public readonly ChannelLease $lease,
        public readonly BackupRun $backupRun,
    ) {
        //
    }
}
