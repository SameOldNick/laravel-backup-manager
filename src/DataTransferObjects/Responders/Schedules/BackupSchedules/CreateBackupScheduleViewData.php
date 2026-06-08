<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules;

use SameOldNick\BackupManager\Models\Collections\FilesystemConfigurationCollection;

class CreateBackupScheduleViewData
{
    public function __construct(
        public readonly FilesystemConfigurationCollection $configurations,
    ) {
        //
    }
}
