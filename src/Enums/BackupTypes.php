<?php

namespace SameOldNick\BackupManager\Enums;

enum BackupTypes: string
{
    case Full = 'full';
    case Files = 'files';
    case Databases = 'databases';

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

    /**
     * Get an array of all accepted backup type values, including legacy values for backward compatibility.
     *
     * @return array An array of accepted backup type string values (e.g. ["full", "only_files", "only_databases", "files", "databases"])
     */
    public static function acceptedValues(): array
    {
        return [...array_column(self::cases(), 'value'), 'only_files', 'only_databases'];
    }

    /**
     * Create a BackupTypes enum instance from a string value.
     *
     * @param  string  $value  The string value to convert (e.g. "full", "only_files", "only_databases")
     * @return BackupTypes The corresponding BackupTypes enum instance
     *
     * @throws \InvalidArgumentException If an invalid backup type value is provided
     */
    public static function fromValue(string $value): self
    {
        return match ($value) {
            'full' => self::Full,
            'files', 'only_files' => self::Files,
            'databases', 'only_databases' => self::Databases,
            default => throw new \InvalidArgumentException("Invalid backup type value: {$value}"),
        };
    }
}
