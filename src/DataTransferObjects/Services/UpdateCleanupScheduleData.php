<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Services;

class UpdateCleanupScheduleData
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $cronExpression,
        public readonly ?bool $isActive,
        public readonly ?array $destinationIds
    ) {
        //
    }

    /**
     * Creates UpdateCleanupScheduleData from array
     *
     * @param  array  $data  Data to create UpdateCleanupScheduleData from
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            cronExpression: $data['cron_expression'] ?? null,
            isActive: $data['is_active'] ?? null,
            destinationIds: $data['destination_ids'] ?? null,
        );
    }
}
