<?php

namespace SameOldNick\BackupManager\Enums;

use App\Components\Notifications\Enums\NotificationTypes;

enum BackupNotificationTypes: string
{
    case BackupFailed = NotificationTypes::BackupFailed->value;

    case BackupSucceeded = NotificationTypes::BackupSucceeded->value;

    case BackupCleanupFailed = NotificationTypes::BackupCleanupFailed->value;

    case BackupCleanupSucceeded = NotificationTypes::BackupCleanupSucceeded->value;

    case BackupHealthyBackupFound = NotificationTypes::BackupHealthyBackupFound->value;

    case BackupUnhealthyBackupFound = NotificationTypes::BackupUnhealthyBackupFound->value;

    /**
     * Convert this enum to its corresponding general NotificationTypes enum.
     */
    public function toNotificationType(): NotificationTypes
    {
        return NotificationTypes::from($this->value);
    }

    /**
     * Get a human-friendly label for the permission.
     */
    public function label(): string
    {
        return match ($this) {
            self::BackupFailed => __('Backup Failed'),
            self::BackupSucceeded => __('Backup Succeeded'),
            self::BackupCleanupFailed => __('Backup Cleanup Failed'),
            self::BackupCleanupSucceeded => __('Backup Cleanup Succeeded'),
            self::BackupHealthyBackupFound => __('Backup Healthy Backup Found'),
            self::BackupUnhealthyBackupFound => __('Backup Unhealthy Backup Found'),
        };
    }
}
