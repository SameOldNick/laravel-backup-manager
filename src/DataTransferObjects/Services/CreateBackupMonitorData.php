<?php

namespace SameOldNick\BackupManager\DataTransferObjects\Services;

class CreateBackupMonitorData
{
    public function __construct(
        public readonly string $name,
        public readonly array $disks,
        public readonly ?int $maximumAgeInDays = null,
        public readonly ?int $maximumStorageInMegabytes = null,
        public readonly bool $enabled = true,
    ) {
    }
}