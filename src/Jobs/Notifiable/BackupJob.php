<?php

namespace SameOldNick\BackupManager\Jobs\Notifiable;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SameOldNick\BackupManager\Broadcasting\Notifiers\ProcessNotifier;
use SameOldNick\BackupManager\Enums\BackupTypes;
use SameOldNick\BackupManager\Enums\RunStatus;
use SameOldNick\BackupManager\Models\BackupRun;
use SameOldNick\BackupManager\SpatieBackup\BackupRunner;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Support\BackupLogger;

class BackupJob extends NotifiableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const BACKUP_FULL = BackupTypes::Full->value;

    const BACKUP_ONLY_FILES = BackupTypes::Files->value;

    const BACKUP_ONLY_DATABASES = BackupTypes::Databases->value;

    public readonly string $backupType;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly string $uuid,
        string $channel,
        object $notifiable,
        BackupTypes $backupType = BackupTypes::Full,
        public readonly ?array $disks = null,
    ) {
        parent::__construct($channel, $notifiable);

        $this->backupType = $backupType->value;
    }

    /**
     * Handle the job.
     */
    public function handle(Config $config, BackupLogger $backupLogger): void
    {
        $this->performJob(function () use ($config, $backupLogger) {
            /** @var BackupRun $backupRun */
            $backupRun = BackupRun::findOrFail($this->uuid);

            $backupRunner = $this->createBackupRunner($backupRun);

            $notifier = ProcessNotifier::create($this->notifiable, $this->channel);

            $this->redirectBackupLoggerMessagesToNotifiable($backupLogger, $notifier);

            try {
                $notifier->begin();

                $backupRunner->run($config, BackupTypes::from($this->backupType), $this->disks);

                $notifier->complete(0);
            } catch (\Exception $e) {
                $notifier->complete(1);

                throw $e;
            }
        });
    }

    /**
     * Creates a BackupRunner instance with callbacks to update the BackupRun status.
     */
    protected function createBackupRunner(BackupRun $backupRun): BackupRunner
    {
        return new BackupRunner(
            onStartedCallback: fn () => $backupRun->update(['status' => RunStatus::Running, 'started_at' => now()]),
            onSuccessCallback: fn () => $backupRun->update(['status' => RunStatus::Successful]),
            onFailedCallback: fn () => $backupRun->update(['status' => RunStatus::Failed]),
            onCompletedCallback: fn () => $backupRun->update(['completed_at' => now()]),
        );
    }

    /**
     * Redirects messages from the backup logger to the notifiable.
     */
    protected function redirectBackupLoggerMessagesToNotifiable(BackupLogger $backupLogger, ProcessNotifier $notifier): void
    {
        $backupLogger->onMessage(function (string $level, string $message) use ($notifier) {
            match ($level) {
                'error' => $notifier->getProcessOutput()->error($message),
                'warning' => $notifier->getProcessOutput()->warn($message),
                default => $notifier->getProcessOutput()->info($message),
            };
        });
    }
}
