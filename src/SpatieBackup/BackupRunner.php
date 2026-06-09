<?php

namespace SameOldNick\BackupManager\SpatieBackup;

use Illuminate\Support\Collection;
use SameOldNick\BackupManager\Enums\BackupRunStatus;
use SameOldNick\BackupManager\Enums\BackupTypes;
use SameOldNick\BackupManager\Models\BackupRun;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Tasks\Backup\BackupJob as SpatieBackupJob;
use Spatie\Backup\Tasks\Backup\BackupJobFactory;

class BackupRunner
{
    /**
     * BackupRunner constructor.
     *
     * @param  ?callable  $onStartedCallback  Optional callback to execute when the backup starts
     * @param  ?callable  $onSuccessCallback  Optional callback to execute when the backup succeeds
     * @param  ?callable  $onFailedCallback   Optional callback to execute when the backup fails
     * @param  ?callable  $onCompletedCallback Optional callback to execute when the backup completes (regardless of success or failure)
     */
    public function __construct(
        protected readonly ?callable $onStartedCallback = null,
        protected readonly ?callable $onSuccessCallback = null,
        protected readonly ?callable $onFailedCallback = null,
        protected readonly ?callable $onCompletedCallback = null,
    ) {
        //
    }

    /**
     * Executes a backup run.
     *
     * @param  ?array<int, string>  $disks
     */
    public function run(Config $config, BackupTypes $backupType = BackupTypes::Full, ?array $disks = null): void
    {
        try {
            $backupJob = $this->createBackupJob($config, $backupType, $disks);

            $backupJob->run();

            if ($this->onSuccessCallback !== null) {
                call_user_func($this->onSuccessCallback);
            }
        } catch (\Exception $e) {
            if ($this->onFailedCallback !== null) {
                call_user_func($this->onFailedCallback, $e);
            }

            throw $e;
        } finally {
            if ($this->onCompletedCallback !== null) {
                call_user_func($this->onCompletedCallback);
            }
        }
        
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
     * Creates a BackupRunner instance with callbacks that update the given BackupRun model.
     *
     * @param  BackupRun  $backupRun  The BackupRun model to update during the backup process
     * @return BackupRunner A BackupRunner instance with callbacks that update the given BackupRun model
     */
    public static function create(BackupRun $backupRun): self
    {
        return new self(
            onStartedCallback: fn () => $backupRun->update(['status' => BackupRunStatus::Running, 'started_at' => now()]),
            onSuccessCallback: fn () => $backupRun->update(['status' => BackupRunStatus::Successful]),
            onFailedCallback: fn () => $backupRun->update(['status' => BackupRunStatus::Failed]),
            onCompletedCallback: fn () => $backupRun->update(['completed_at' => now()]),
        );
    }
}
