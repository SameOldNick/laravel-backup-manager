<?php

namespace SameOldNick\BackupManager\Runners;

use Illuminate\Support\Collection;
use SameOldNick\BackupManager\Enums\BackupTypes;
use SameOldNick\BackupManager\Enums\RunStatus;
use SameOldNick\BackupManager\Models\BackupRun;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Tasks\Backup\BackupJob as SpatieBackupJob;
use Spatie\Backup\Tasks\Backup\BackupJobFactory;

class BackupRunner extends Runner
{
    /**
     * Executes a backup run.
     *
     * @param  ?array<int, string>  $disks
     */
    public function run(Config $config, BackupTypes $backupType = BackupTypes::Full, ?array $disks = null): void
    {
        $this->executeWithCallbacks(function () use ($config, $backupType, $disks) {
            $backupJob = $this->createBackupJob($config, $backupType, $disks);

            $backupJob->run();
        });
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

    /**
     * Create a BackupRunner with callbacks wired to update the given BackupRun model.
     */
    public static function forBackupRun(BackupRun $backupRun): self
    {
        return new self(
            onStartedCallback: static fn () => $backupRun->update(['status' => RunStatus::Running, 'started_at' => now()]),
            onSuccessCallback: static fn () => $backupRun->update(['status' => RunStatus::Successful]),
            onFailedCallback: static fn () => $backupRun->update(['status' => RunStatus::Failed]),
            onCompletedCallback: static fn () => $backupRun->update(['completed_at' => now()]),
        );
    }
}
