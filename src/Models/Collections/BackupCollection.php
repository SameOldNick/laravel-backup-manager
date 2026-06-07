<?php

namespace SameOldNick\BackupManager\Models\Collections;

use Illuminate\Database\Eloquent\Collection;
use SameOldNick\BackupManager\Concerns\PaginatesCollection;
use SameOldNick\BackupManager\Models\Backup;

/**
 * @extends Collection<int, Backup>
 */
class BackupCollection extends Collection
{
    use PaginatesCollection;

    /**
     * Gets backups with status
     *
     * @return static
     */
    public function status(string $status)
    {
        return $this->filter(fn (Backup $backup) => $backup->status === $status);
    }
}
