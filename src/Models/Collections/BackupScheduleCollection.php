<?php

namespace SameOldNick\BackupManager\Models\Collections;

use Illuminate\Database\Eloquent\Collection;
use SameOldNick\BackupManager\Concerns\PaginatesCollection;
use SameOldNick\BackupManager\Models\BackupSchedule;

/**
 * @extends Collection<int, BackupSchedule>
 */
class BackupScheduleCollection extends Collection
{
    use PaginatesCollection;
}
