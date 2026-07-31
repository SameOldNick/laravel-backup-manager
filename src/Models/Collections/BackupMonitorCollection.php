<?php

namespace SameOldNick\BackupManager\Models\Collections;

use Illuminate\Database\Eloquent\Collection;
use SameOldNick\BackupManager\Concerns\PaginatesCollection;
use SameOldNick\BackupManager\Models\BackupMonitor;

/**
 * @extends Collection<int, BackupMonitor>
 */
class BackupMonitorCollection extends Collection
{
    use PaginatesCollection;
}