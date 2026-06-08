<?php

namespace SameOldNick\BackupManager\Models\Collections;

use Illuminate\Database\Eloquent\Collection;
use SameOldNick\BackupManager\Concerns\PaginatesCollection;
use SameOldNick\BackupManager\Models\CleanupSchedule;

/**
 * @extends Collection<int, CleanupSchedule>
 */
class CleanupScheduleCollection extends Collection
{
    use PaginatesCollection;
}
