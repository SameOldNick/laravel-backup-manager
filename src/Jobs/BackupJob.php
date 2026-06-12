<?php

namespace SameOldNick\BackupManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SameOldNick\BackupManager\Enums\BackupTypes;
use SameOldNick\BackupManager\Runners\BackupRunner;
use Spatie\Backup\Config\Config;

class BackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const BACKUP_FULL = BackupTypes::Full->value;

    const BACKUP_ONLY_FILES = BackupTypes::Files->value;

    const BACKUP_ONLY_DATABASES = BackupTypes::Databases->value;

    /**
     * @var string The type of backup to perform, represented as a string value of the BackupTypes enum
     */
    public readonly string $backupType;

    /**
     * Create a new job instance.
     */
    public function __construct(
        BackupTypes $backupType = BackupTypes::Full,
        public readonly ?array $disks = null,
    ) {
        $this->backupType = $backupType->value;
    }

    /**
     * Execute the job.
     */
    public function handle(Config $config, BackupRunner $backupRunner): void
    {
        $backupRunner($config, BackupTypes::from($this->backupType), $this->disks);
    }
}
