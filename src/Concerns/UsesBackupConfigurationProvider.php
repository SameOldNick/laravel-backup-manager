<?php

namespace SameOldNick\BackupManager\Concerns;

use SameOldNick\BackupManager\Contracts\BackupConfigurationProvider;

trait UsesBackupConfigurationProvider
{
    /**
     * Gets the backup configuration provider
     */
    protected function getConfigurationProvider(): BackupConfigurationProvider
    {
        return app(BackupConfigurationProvider::class);
    }
}
