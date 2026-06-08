<?php

namespace SameOldNick\BackupManager\DataTransferObjects;

use SameOldNick\BackupManager\Enums\BackupTypes;

class UpdateBackupScheduleData
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?BackupTypes $type,
        public readonly ?string $cronExpression,
        public readonly ?bool $isActive,
        public readonly ?array $destinationIds
    ) {
        //
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            type: $data['type'] ? BackupTypes::tryFrom($data['type']) : null,
            cronExpression: $data['cron_expression'] ?? null,
            isActive: $data['is_active'] ?? null,
            destinationIds: $data['destination_ids'] ?? null,
        );
    }
}
