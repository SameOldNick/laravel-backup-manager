<?php

namespace SameOldNick\BackupManager\SpatieBackup;

use SameOldNick\BackupManager\Models\BackupMonitor;
use Spatie\Backup\Config\MonitoredBackupConfig;
use Spatie\Backup\Config\MonitoredBackupsConfig;

class DatabaseMonitoredBackupsConfigProvider extends MonitoredBackupsConfig
{
    public function __construct(protected readonly MonitoredBackupsConfig $original)
    {
        parent::__construct(
            monitoredBackups: $this->getMonitoredBackupConfigs(),
        );
    }

    protected function getMonitoredBackupConfigs(): array
    {
        $monitors = BackupMonitor::query()->where('is_active', true)->get();

        if ($monitors->isEmpty()) {
            return $this->original->monitoredBackups;
        }

        return $monitors->map(function (BackupMonitor $monitor) {
            return new MonitoredBackupConfig(
                name: $monitor->name,
                disks: $monitor->disks,
                healthChecks: $monitor->getHealthChecks(),
            );
        })->all();
    }
}