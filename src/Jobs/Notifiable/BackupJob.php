<?php

namespace SameOldNick\BackupManager\Jobs\Notifiable;

use SameOldNick\BackupManager\Broadcasting\Notifiers\ProcessNotifier;
use SameOldNick\BackupManager\SpatieBackup\BackupRunner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Support\BackupLogger;

class BackupJob extends NotifiableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const BACKUP_FULL = BackupRunner::BACKUP_FULL;

    const BACKUP_ONLY_FILES = BackupRunner::BACKUP_ONLY_FILES;

    const BACKUP_ONLY_DATABASES = BackupRunner::BACKUP_ONLY_DATABASES;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $channel,
        object $notifiable,
        public readonly string $backupType = self::BACKUP_FULL,
        public readonly ?array $disks = null,
    ) {
        parent::__construct($channel, $notifiable);
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

                $backupRunner->run($config, $this->backupType, $this->disks);

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
