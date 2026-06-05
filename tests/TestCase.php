<?php

namespace SameOldNick\BackupManager\Tests;

use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Router;
use Orchestra\Testbench\Concerns\WithLaravelMigrations;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;
use SameOldNick\BackupManager\Contracts\Responders as UiResponderContracts;
use SameOldNick\BackupManager\Testing\Responders as TestResponders;
use Workbench\App\Models\User;

class TestCase extends Orchestra
{
    use RefreshDatabase;
    use WithLaravelMigrations;
    use WithWorkbench;

    /**
     * Automatically enables package discoveries.
     *
     * @var bool
     */
    protected $enablesPackageDiscoveries = true;

    public function getEnvironmentSetUp($app)
    {
        $this->registerUiResponders($app);

        tap($app['config'], function (Repository $config) {
            $config->set('database.default', 'testing');

            $config->set('backup', require __DIR__.'/../config/backup.php');
            $config->set('backup-manager', require __DIR__.'/../config/backup-manager.php');
        });
    }

    /**
     * Register the UI responders to use the test versions for testing.
     *
     * @return void
     */
    protected function registerUiResponders(Application $app)
    {
        $app->bind(UiResponderContracts\BackupDestinationsUiResponder::class, TestResponders\BackupDestinationsUiResponder::class);
        $app->bind(UiResponderContracts\BackupSchedulesUiResponder::class, TestResponders\BackupSchedulesUiResponder::class);
        $app->bind(UiResponderContracts\BackupsUiResponder::class, TestResponders\BackupsUiResponder::class);
        $app->bind(UiResponderContracts\CleanupSchedulesUiResponder::class, TestResponders\CleanupSchedulesUiResponder::class);
        $app->bind(UiResponderContracts\SchedulesUiResponder::class, TestResponders\SchedulesUiResponder::class);
    }

    /**
     * Define routes setup.
     *
     * @param  Router  $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        require __DIR__.'/../routes/web.php';
        require __DIR__.'/../routes/channels.php';
    }

    /**
     * Helper method to create an admin user for testing.
     */
    protected function createAdmin(): User
    {
        $admin = User::factory()->create();

        return $admin;
    }
}
