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
            monitorBackups: $this->getMonitoredBackupConfigs(),
        );
    }

    protected function getMonitoredBackupConfigs(): array
    {
        $monitors = BackupMonitor::query()->where('is_active', true)->get();

        if ($monitors->isEmpty()) {
            return $this->original->monitorBackups;
        }

        return $monitors->map(function (BackupMonitor $monitor) {
            $disks = $monitor->filesystemConfigurations()->where('is_active', true)->pluck('driver_name')->toArray();

            return MonitoredBackupConfig::fromArray([
                'name' => $monitor->name,
                'disks' => $disks,
                'healthChecks' => $monitor->getHealthChecks(),
            ]);
        })->all();
    }
}
