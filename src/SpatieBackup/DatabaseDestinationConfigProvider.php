<?php

namespace SameOldNick\BackupManager\SpatieBackup;

use Illuminate\Support\Facades\Log;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use Spatie\Backup\Config\DestinationConfig;

class DatabaseDestinationConfigProvider extends DestinationConfig
{
    /**
     * Create a new DatabaseDestinationConfigProvider instance.
     *
     * @param  DestinationConfig  $original  The original DestinationConfig instance to wrap
     */
    public function __construct(protected readonly DestinationConfig $original)
    {
        parent::__construct(
            compressionMethod: $original->compressionMethod,
            compressionLevel: $original->compressionLevel,
            filenamePrefix: $original->filenamePrefix,
            disks: $this->getDisks(),
            continueOnFailure: $original->continueOnFailure,
        );
    }

    /**
     * Gets disks to use for backups
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
