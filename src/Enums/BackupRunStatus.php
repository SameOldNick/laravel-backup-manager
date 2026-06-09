<?php

namespace SameOldNick\BackupManager\Enums;

enum BackupRunStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Successful = 'successful';
    case Failed = 'failed';

    /**
     * Get a human-friendly label for the permission.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Running => __('Running'),
            self::Successful => __('Successful'),
            self::Failed => __('Failed'),
        };
    }
}
