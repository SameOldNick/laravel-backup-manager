<?php

namespace SameOldNick\BackupManager;

use Exception;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use SameOldNick\BackupManager\Commands\InstallBackupManager;
use SameOldNick\BackupManager\DbDumper\MySqlPHP;
use Spatie\Backup\Tasks\Backup\DbDumperFactory;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register any application services.
     *
     * Container bindings are deferred to {@see DeferredServiceProvider}
     * to avoid loading them on every request.
     *
     * @return void
     */
    public function register()
    {
        //
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
