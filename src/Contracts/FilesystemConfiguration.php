<?php

namespace SameOldNick\BackupManager\Contracts;

use Spatie\Backup\Config\Config;

interface FilesystemConfiguration
{
    /**
     * Gets the filesystem configuration.
     * The array will have the same structure as the drivers/disks in config\filesystems.php
     */
    public function getFilesystemConfig(): array;

    /**
     * Determine if the destination is enabled based on the Spatie Backup config.
     */
    public function isEnabled(Config $backupConfig): bool;
}
