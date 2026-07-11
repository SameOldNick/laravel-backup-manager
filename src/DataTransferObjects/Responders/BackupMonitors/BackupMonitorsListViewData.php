<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors;

use SameOldNick\BackupManager\Models\Collections\BackupMonitorCollection;

class BackupMonitorsListViewData
{
    public function __construct(
        public readonly BackupMonitorCollection $backupMonitors,
    ) {
    }
}