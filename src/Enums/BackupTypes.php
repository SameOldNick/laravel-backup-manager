<?php

namespace SameOldNick\BackupManager\Enums;

enum BackupTypes: string
{
    case Full = 'full';
    case Files = 'only_files';
    case Databases = 'only_databases';

    /**
     * Get a human-friendly label for the permission.
     */
    public function label(): string
    {
        return match ($this) {
            self::Full => __('Full Backup'),
            self::Files => __('Files Backup'),
            self::Databases => __('Databases Backup'),
        };
    }
}
