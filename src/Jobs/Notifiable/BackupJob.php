<?php

namespace SameOldNick\BackupManager\Jobs\Notifiable;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SameOldNick\BackupManager\Broadcasting\Notifiers\ProcessNotifier;
use SameOldNick\BackupManager\Enums\BackupTypes;
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
    public function handle(Config $config, BackupRunner $backupRunner, BackupLogger $backupLogger): void
    {
        $this->performJob(function () use ($backupRunner, $config, $backupLogger) {
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
