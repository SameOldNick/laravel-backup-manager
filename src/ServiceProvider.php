<?php

namespace SameOldNick\BackupManager;

use Exception;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Filesystem\Factory as FactoryContract;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use SameOldNick\BackupManager\Broadcasting\Access\ChannelAccessManager;
use SameOldNick\BackupManager\Broadcasting\Access\Stores\CacheStore;
use SameOldNick\BackupManager\Commands\InstallBackupManager;
use SameOldNick\BackupManager\Contracts\BackupConfigurationProvider;
use SameOldNick\BackupManager\Contracts\ChannelAccessStore;
use SameOldNick\BackupManager\Contracts\ConfigProvider;
use SameOldNick\BackupManager\DbDumper\MySqlPHP;
use SameOldNick\BackupManager\Filesystem\DynamicFilesystemManager;
use SameOldNick\BackupManager\Providers\BackupDatabaseConfigurationProvider;
use SameOldNick\BackupManager\SpatieBackup\DatabaseConfigProvider;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Tasks\Backup\DbDumperFactory;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * The service provider may be called before the database is setup.
         * If the case, the app will fail so we'll let it pull it from the config
         * until the database is setup. This happens usually with testing.
         */
        $this->rebindSpatieBackupConfig();
        $this->extendFilesystemManager();

        $this->bindContracts();

        $this->app->bind(ChannelAccessStore::class, function (Container $app) {
            return new CacheStore($app->make(Repository::class));
        });

        $this->app->singleton(ChannelAccessManager::class, function (Container $app) {
            return new ChannelAccessManager($app->make(ChannelAccessStore::class));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallBackupManager::class,
            ]);
        }

        if (config('backup-manager.routes.enabled', true)) {
            $this->registerRoutes();
        }

        $this->publishes([
            __DIR__.'/../config/backup-manager.php' => $this->app->configPath('backup-manager.php'),
        ], 'backup-manager-config');

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'backup-manager-migrations');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'backup-manager');
        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/backup-manager'),
        ], 'backup-manager-translations');

        $this->bootDbDumperExtender();
        $this->scheduleBackups();
        $this->subscribeToEvents();
    }

    /**
     * Registers routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/channels.php');
    }

    /**
     * Subscribes to events.
     *
     * @return void
     */
    protected function subscribeToEvents()
    {
        Event::subscribe(Listeners\BackupProjectionSubscriber::class);
    }

    /**
     * Checks if database has been setup by checking if the specified tables exist.
     */
    protected function isDatabaseSetup(array $tables): bool
    {
        try {
            // If the database is not setup, this will throw an exception, which we catch and return false.
            // We don't need to check if the connection is available because if it's not, the exception will be thrown when we try to check for the tables.
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
     *
     * @return void
     */
    protected function rebindSpatieBackupConfig()
    {
        // Ensure a base binding exists, regardless of whether Spatie's provider has run.
        // If Spatie already bound it, this is a no-op (scoped replaces, but same result).
        if (! $this->app->bound(Config::class)) {
            $this->app->scoped(Config::class, fn (): Config => Config::fromArray(config('backup')));
        }

        // Extenders are stored on the abstract and applied at resolution time.
        // They survive subsequent rebinds, so this works regardless of provider order.
        $this->app->extend(Config::class, function (Config $config, Container $app): Config {
            if ($this->isDatabaseSetup(['filesystem_configurations'])) {
                return $app->make(DatabaseConfigProvider::class, ['original' => $config]);
            }

            return $config;
        });
    }

    /**
     * Extends Filesystem Manager
     *
     * @return void
     */
    protected function extendFilesystemManager()
    {
        $this->app->extend(FactoryContract::class, function (FactoryContract $manager, Container $app) {
            return new DynamicFilesystemManager($app);
        });
    }

    /**
     * Binds interfaces to implementations.
     *
     * @return void
     */
    protected function bindContracts()
    {
        $this->app->bind(ConfigProvider::class, DatabaseConfigProvider::class);
        $this->app->bind(BackupConfigurationProvider::class, BackupDatabaseConfigurationProvider::class);
    }

    /**
     * Adds MySqlPHP to create MySQL dump
     *
     * @return void
     */
    protected function bootDbDumperExtender()
    {
        $extenders = config('backup-manager.db_dumper_extenders', []);

        if (! is_array($extenders) || empty($extenders)) {
            return;
        }

        foreach ($extenders as $type => $class) {
            DbDumperFactory::extend($type, function () use ($class) {
                return $this->app->make($class);
            });
        }
    }

    /**
     * Sets up backup scheduler
     *
     * @return void
     */
    protected function scheduleBackups()
    {
        if ($this->app->runningInConsole() &&
            $this->isDatabaseSetup([
                'backup_schedules',
                'cleanup_schedules',
                'backup_schedule_filesystem_configuration',
            ])) {
            $this->app->make(BackupScheduler::class)->schedule();
        }
    }
}
