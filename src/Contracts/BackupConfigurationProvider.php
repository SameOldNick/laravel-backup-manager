<?php

namespace SameOldNick\BackupManager\Contracts;

interface BackupConfigurationProvider
{
    /**
     * Gets disks to use for backups
     */
    public function getDisks(): array;
}
