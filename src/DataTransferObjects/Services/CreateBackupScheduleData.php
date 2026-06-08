<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Services;

use SameOldNick\BackupManager\Enums\BackupTypes;

class CreateBackupScheduleData
{
    public function __construct(
        public readonly string $name,
        public readonly BackupTypes $type,
        public readonly string $cronExpression,
        public readonly bool $isActive,
        public readonly ?array $destinationIds
    ) {
        //
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            type: BackupTypes::tryFrom($data['type']),
            cronExpression: $data['cron_expression'],
            isActive: $data['is_active'] ?? false,
            destinationIds: $data['destination_ids'] ?? null,
        );
    }
}
