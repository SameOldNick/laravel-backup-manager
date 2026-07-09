<?php

namespace SameOldNick\BackupManager\Filesystem;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Str;
use SameOldNick\BackupManager\Contracts\FilesystemConfiguration as FilesystemConfigurationContract;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;

class DynamicFilesystemManager extends FilesystemManager
{
    /**
     * {@inheritDoc}
     */
    protected function resolve($name, $config = null)
    {
        if (Str::startsWith($name, 'dynamic-') && $config = $this->getDynamicFilesystemConfiguration($name)) {

            return $this->createCustomDriver($this->getDriverConfig($config));
        }

        // Fallback to parent if no dynamic config is found
        return parent::resolve($name);
    }

    /**
     * Gets filesystem configuration for dynamic disk.
     *
     * @param  string  $name  Disk name (prefixed with dynamic-)
     */
    protected function getDynamicFilesystemConfiguration(string $name): ?FilesystemConfigurationContract
    {
        // Expecting name to be in format "dynamic-{disk_name}"
        return FilesystemConfiguration::active()->byDriverName($name)->first();
    }

    /**
     * Creates driver from config
     *
     * @return Filesystem
     */
    protected function createCustomDriver(array $config)
    {
        // $config must have 'driver' key set
        // If not, the parent resolve method will thrown an exception

        return parent::resolve($config['name'], $config);
    }

    /**
     * Gets filesystem config from FilesystemConfigurationContract
     */
    protected function getDriverConfig(FilesystemConfigurationContract $config): array
    {
        return $config->getFilesystemConfig();
    }
}
