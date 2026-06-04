<?php

namespace SameOldNick\BackupManager\Listeners;

use SameOldNick\BackupManager\Models\Backup;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Auth;
use Spatie\Backup\BackupDestination\Backup as SpatieBackup;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\CleanupWasSuccessful;

class BackupProjectionSubscriber
{
    /**
     * Handle the successful backup event.
     */
    public function handleSuccessfulBackup(BackupWasSuccessful $event): void
    {
        $backupDestination = $this->getBackupDestination($event);

        $backupModel = Backup::create();

        if ($newestBackup = $backupDestination->newestBackup()) {
            $backupModel->file()->save(Backup::createFile(
                $newestBackup,
                $backupDestination,
                Auth::user()
            ));
        }
    }

    /**
     * Handle the failed backup event.
     */
    public function handleFailedBackup(BackupHasFailed $event): void
    {
        Backup::create([
            'error_message' => $event->exception->getMessage(),
        ]);
    }

    /**
     * Handle the successful cleanup event.
     */
    public function handleSuccessfulCleanup(CleanupWasSuccessful $event): void
    {
        $backupDestination = $this->getBackupDestination($event);
        $existing = $backupDestination->backups();

        foreach (Backup::all() as $model) {
            $found = $existing->first(function (SpatieBackup $backup) use ($model, $backupDestination) {
                if (is_null($model->file) || $backupDestination->diskName() !== $model->file->disk) {
                    return false;
                }

                return $model->file->path === $backup->path();
            });

            // Test if exists() is false, as it will test the 'exists' property which is set to false when delete is called.
            if (! is_null($found) && ! $found->exists()) {
                $model->delete();
            }
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @return array<string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            BackupWasSuccessful::class => 'handleSuccessfulBackup',
            BackupHasFailed::class => 'handleFailedBackup',
            CleanupWasSuccessful::class => 'handleSuccessfulCleanup',
        ];
    }

    /**
     * Get the backup destination instance from the event.
     */
    protected function getBackupDestination(BackupWasSuccessful|CleanupWasSuccessful $event): BackupDestination
    {
        return BackupDestination::create($event->diskName, $event->backupName);
    }
}
