<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors;

class DestroyBackupMonitorViewData
{
    public function __construct(
        public readonly int $id,
    ) {
    }
}