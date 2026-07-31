<?php

namespace SameOldNick\BackupManager\Testing\Concerns;

use Spatie\Backup\Config\Config;
use Spatie\Backup\Config\MonitoredBackupsConfig;

trait MonitorTestHelpers
{
    /**
     * Get the monitored backups configuration.
     */
    protected function getMonitoredBackupsConfig(): MonitoredBackupsConfig
    {
        return app(Config::class)->monitoredBackups;
    }

    /**
     * Assert that the monitored backups match the given criteria.
     *
     * @param  callable(array<array{name: string, disks: array, healthChecks: array}>): void  $callback
     */
    protected function assertMonitoredBackups(callable $callback): void
    {
        $config = $this->getMonitoredBackupsConfig();

        $callback($config->monitorBackups);
    }
}
