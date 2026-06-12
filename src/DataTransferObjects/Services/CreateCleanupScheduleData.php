<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Services;

class CreateCleanupScheduleData
{
    public function __construct(
        public readonly string $name,
        public readonly string $cronExpression,
        public readonly bool $isActive,
    ) {
        //
    }

    /**
     * Creates CreateCleanupScheduleData from array
     *
     * @param  array  $data  Data to create CreateCleanupScheduleData from
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            cronExpression: $data['cron_expression'],
            isActive: $data['is_active'] ?? false,
        );
    }
}
