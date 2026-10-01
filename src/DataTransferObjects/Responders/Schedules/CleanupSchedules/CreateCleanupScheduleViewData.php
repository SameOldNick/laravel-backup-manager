<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules;

use SameOldNick\BackupManager\Models\Collections\FilesystemConfigurationCollection;

class CreateCleanupScheduleViewData
{
    public function __construct(
        public readonly FilesystemConfigurationCollection $configurations,
    ) {
        //
    }
}
