<?php

namespace SameOldNick\BackupManager\SpatieBackup;

use SameOldNick\BackupManager\Concerns\UsesBackupConfigurationProvider;
use Spatie\Backup\Config\DestinationConfig;

class DatabaseDestinationConfigProvider extends DestinationConfig
{
    use UsesBackupConfigurationProvider;

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
        return $this->getConfigurationProvider()->getDisks();
    }
}
