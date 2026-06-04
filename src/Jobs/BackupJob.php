<?php

namespace SameOldNick\BackupManager\Jobs;

use SameOldNick\BackupManager\SpatieBackup\BackupRunner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Backup\Config\Config;

class BackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const BACKUP_FULL = BackupRunner::BACKUP_FULL;

    const BACKUP_ONLY_FILES = BackupRunner::BACKUP_ONLY_FILES;

    const BACKUP_ONLY_DATABASES = BackupRunner::BACKUP_ONLY_DATABASES;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly string $backupType = self::BACKUP_FULL,
        public readonly ?array $disks = null,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(Config $config, BackupRunner $backupRunner): void
    {
        $backupRunner->run($config, $this->backupType, $this->disks);
    }
}
