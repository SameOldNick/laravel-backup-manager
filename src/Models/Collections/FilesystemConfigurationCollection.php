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

    /**
     * Get the active and valid configurations.
     */
    public function getActiveAndValid(): self
    {
        return $this->filter(fn (FilesystemConfiguration $config) => $config->is_active && $config->is_valid);
    }

    /**
     * Get the valid configurations.
     */
    public function getValid(): self
    {
        return $this->filter(fn (FilesystemConfiguration $config) => $config->is_valid);
    }

    /**
     * Get the active configurations.
     */
    public function getActive(): self
    {
        return $this->filter(fn (FilesystemConfiguration $config) => $config->is_active);
    }

    /**
     * Get the names of the disks for the configurations.
     *
     * @return array<int, string>
     */
    public function toDiskNames(): array
    {
        return $this->map(fn (FilesystemConfiguration $config) => $config->driver_name)
            ->values()
            ->all();
    }
}
