<?php

namespace SameOldNick\BackupManager\SpatieBackup;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use SameOldNick\BackupManager\Models\BackupMonitor;
use Spatie\Backup\Config\MonitoredBackupConfig;
use Spatie\Backup\Config\MonitoredBackupsConfig;

class DatabaseMonitoredBackupsConfigProvider extends MonitoredBackupsConfig
{
    /**
     * Create a new instance of the DatabaseMonitoredBackupsConfigProvider.
     */
    public function __construct(protected readonly MonitoredBackupsConfig $original)
    {
        parent::__construct(
            monitorBackups: $this->getMonitoredBackupConfigs(),
        );
    }

    /**
     * Get monitored backup configs
     */
    protected function getMonitoredBackupConfigs(): array
    {
        try {
            $monitors = BackupMonitor::query()->where('is_active', true)->get();

            if ($monitors->isEmpty()) {
                return $this->getFallbackMonitorBackups();
            }

            return $monitors->map(function (BackupMonitor $monitor) {
                $disks = $monitor->filesystemConfigurations()
                    ->active()
                    ->get()
                    ->pluck('driver_name')
                    ->all();

                return MonitoredBackupConfig::fromArray([
                    'name' => $monitor->name,
                    'disks' => $disks,
                    'health_checks' => $monitor->getHealthChecks(),
                ]);
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Error fetching monitored backups from database: '.$e->getMessage());

            return $this->getFallbackMonitorBackups();
        }
    }

    /**
     * Get the fallback monitored backups from the original configuration.
     *
     * @return array<MonitoredBackupConfig>
     */
    protected function getFallbackMonitorBackups(): array
    {
        $fallback = config('backup-manager.config_fallbacks.monitor_backups');

        if (is_array($fallback)) {
            return Arr::map($fallback, fn (array $monitoredBackup) => MonitoredBackupConfig::fromArray($monitoredBackup));
        } elseif ($fallback) {
            return $this->original->monitorBackups;
        }

        return [];
    }
}
