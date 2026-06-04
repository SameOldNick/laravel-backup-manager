<?php

namespace SameOldNick\BackupManager\Models\Collections;

use SameOldNick\BackupManager\Models\Backup;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends Collection<int, Backup>
 */
class BackupCollection extends Collection
{
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
