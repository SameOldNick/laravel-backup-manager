<?php

namespace SameOldNick\BackupManager\Enums;

enum BackupStatus: string
{
    case Successful = 'successful';
    case Failed = 'failed';
    case Deleted = 'deleted';
    case FileNotFound = 'file_not_found';

    /**
     * Get a human-friendly label for the permission.
     */
    public function label(): string
    {
        return match ($this) {
            self::Successful => __('Successful'),
            self::Failed => __('Failed'),
            self::Deleted => __('Deleted'),
            self::FileNotFound => __('File Not Found'),
        };
    }
}
