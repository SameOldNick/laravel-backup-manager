<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors;

use SameOldNick\BackupManager\Models\BackupMonitor;

class UpdateBackupMonitorViewData
{
    public function __construct(
        public readonly BackupMonitor $backupMonitor,
    ) {
    }
}