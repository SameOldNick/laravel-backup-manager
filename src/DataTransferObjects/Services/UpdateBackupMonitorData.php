<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Services;

class UpdateBackupMonitorData
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?array $disks = null,
        public readonly ?int $maximumAgeInDays = null,
        public readonly ?int $maximumStorageInMegabytes = null,
        public readonly ?bool $enabled = null,
    ) {
    }
}