<?php

namespace SameOldNick\BackupManager\SpatieBackup;

use Illuminate\Support\Collection;
use SameOldNick\BackupManager\Enums\BackupTypes;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Tasks\Backup\BackupJob as SpatieBackupJob;
use Spatie\Backup\Tasks\Backup\BackupJobFactory;

class BackupRunner
{
    /**
     * Executes a backup run.
     *
     * @param  ?array<int, string>  $disks
     */
    public function run(Config $config, BackupTypes $backupType = BackupTypes::Full, ?array $disks = null): void
    {
        $backupJob = $this->createBackupJob($config, $backupType, $disks);

        $backupJob->run();
    }

    /**
     * Creates backup job based on the backup type and disks.
     *
     * @param  ?array<int, string>  $disks
     */
    public function createBackupJob(Config $config, BackupTypes $backupType = BackupTypes::Full, ?array $disks = null): SpatieBackupJob
    {
        $backupJob = BackupJobFactory::createFromConfig($config)
            ->disableSignals();

        $backupJob = match ($backupType) {
            BackupTypes::Files => $backupJob->dontBackupDatabases(),
            BackupTypes::Databases => $backupJob->dontBackupFilesystem(),
            default => $backupJob,
        };

        if ($disks !== null && count($disks) > 0) {
            $backupJob->setBackupDestinations($this->createBackupDestinations($disks, $config->backup->name));
        }

        return $backupJob;
    }

    /**
     * @param  array<int, string>  $disks
     * @return Collection<int, BackupDestination>
     */
    protected function createBackupDestinations(array $disks, string $backupName): Collection
    {
        return collect($disks)
            ->map(fn (string $filesystemName) => BackupDestination::create($filesystemName, $backupName));
    }
}
