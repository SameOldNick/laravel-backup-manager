<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup;

use SameOldNick\BackupManager\Broadcasting\Access\ChannelLease;

class PerformBackupViewData
{
    public function __construct(
        public readonly string $type,
        public readonly string $uuid,
        public readonly ?ChannelLease $lease,
    ) {
        //
    }
}
