<?php

namespace SameOldNick\BackupManager;

use Exception;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use SameOldNick\BackupManager\Broadcasting\Access\ChannelAccessManager;
use SameOldNick\BackupManager\Broadcasting\Access\Stores\CacheStore;
use SameOldNick\BackupManager\Contracts\BackupConfigurationProvider;
use SameOldNick\BackupManager\Contracts\ChannelAccessStore;
use SameOldNick\BackupManager\Contracts\ConfigProvider;
use SameOldNick\BackupManager\Providers\BackupDatabaseConfigurationProvider;
use SameOldNick\BackupManager\SpatieBackup\DatabaseConfigProvider;
use Spatie\Backup\Config\Config;

/**
 * Deferred service provider for backup-manager container bindings.
 *
 * This provider is only loaded when one of its bindings is actually
 * resolved from the container, avoiding the cost of registering
 * these bindings on every request.
 */
class DeferredServiceProvider extends BaseServiceProvider implements DeferrableProvider
{
    /**
     * Register container bindings.
     *
     * @return void
     */
    public function register()
    {
        $this->rebindSpatieBackupConfig();
        $this->bindContracts();
        $this->bindChannelServices();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            Config::class,
            ConfigProvider::class,
            BackupConfigurationProvider::class,
            ChannelAccessStore::class,
            ChannelAccessManager::class,
        ];
    }

    /**
     * Checks if database has been setup by checking if the specified tables exist.
     */
    protected function isDatabaseSetup(array $tables): bool
    {
        try {
            foreach ($tables as $table) {
                if (! Schema::hasTable($table)) {
                    return false;
                }
            }

            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Rebinds Spatie Backup Config to use database-driven configuration when available.
     *
     * Uses a two-layer strategy to handle unknown provider registration order:
     *   1. scoped() — ensures Config is always bound (fallback if Spatie hasn't run yet).
     *   2. extend() — wraps the final resolved instance with DatabaseConfigProvider when
     *      the database is ready.  extend() callbacks survive rebinding, so even if
     *      Spatie's provider runs after ours and replaces the scoped binding, the
     *      extender still fires at resolution time.
     */
    protected function rebindSpatieBackupConfig(): void
    {
        if (! $this->app->bound(Config::class)) {
            $this->app->scoped(Config::class, fn (): Config => Config::fromArray(config('backup')));
        }

        $this->app->extend(Config::class, function (Config $config, Container $app): Config {
            if ($this->isDatabaseSetup(['filesystem_configurations'])) {
                return $app->make(DatabaseConfigProvider::class, ['original' => $config]);
            }

            return $config;
        });
    }

    /**
     * Binds interfaces to implementations.
     */
    protected function bindContracts(): void
    {
        $this->app->bind(ConfigProvider::class, DatabaseConfigProvider::class);
        $this->app->bind(BackupConfigurationProvider::class, BackupDatabaseConfigurationProvider::class);
    }

    /**
     * Binds broadcasting channel services.
     */
    protected function bindChannelServices(): void
    {
        $this->app->bind(ChannelAccessStore::class, function (Container $app) {
            return new CacheStore($app->make(Repository::class));
        });

        $this->app->singleton(ChannelAccessManager::class, function (Container $app) {
            return new ChannelAccessManager($app->make(ChannelAccessStore::class));
        });
    }
}
