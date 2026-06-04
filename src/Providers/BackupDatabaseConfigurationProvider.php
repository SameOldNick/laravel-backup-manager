<?php

namespace SameOldNick\BackupManager\Providers;

use SameOldNick\BackupManager\Contracts\BackupConfigurationProvider;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use Illuminate\Support\Facades\Log;

class BackupDatabaseConfigurationProvider implements BackupConfigurationProvider
{
    /**
     * {@inheritDoc}
     */
    public function getDisks(): array
    {
        try {
            return FilesystemConfiguration::where('is_active', true)->get()->map(
                fn (FilesystemConfiguration $config) => $config->driver_name
            )->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to fetch active filesystem configurations for backup disks: '.$e->getMessage());

            return [];
        }

    }
}
