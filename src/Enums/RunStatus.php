<?php

namespace SameOldNick\BackupManager\Enums;

enum RunStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Successful = 'successful';
    case Failed = 'failed';

    /**
     * Get a human-friendly label.
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
