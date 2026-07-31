<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors;

use SameOldNick\BackupManager\Models\BackupMonitor;

class StoreBackupMonitorViewData
{
    public function __construct(
        public readonly BackupMonitor $backupMonitor,
    ) {
    }
}
