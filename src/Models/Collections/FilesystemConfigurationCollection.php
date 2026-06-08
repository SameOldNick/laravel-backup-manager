<?php

namespace SameOldNick\BackupManager\Models\Collections;

use Illuminate\Database\Eloquent\Collection;
use SameOldNick\BackupManager\Concerns\PaginatesCollection;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;

/**
 * @extends Collection<int, FilesystemConfiguration>
 */
class FilesystemConfigurationCollection extends Collection
{
    use PaginatesCollection;
}
