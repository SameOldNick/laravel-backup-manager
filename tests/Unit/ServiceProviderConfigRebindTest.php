<?php

namespace SameOldNick\BackupManager\Tests\Unit;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use SameOldNick\BackupManager\SpatieBackup\DatabaseBackupConfigProvider;
use SameOldNick\BackupManager\SpatieBackup\DatabaseConfigProvider;
use SameOldNick\BackupManager\Tests\TestCase;
use Spatie\Backup\Config\Config;

class ServiceProviderConfigRebindTest extends TestCase
{
    /**
     * Reset Config container state before each simulation to prevent
     * extender accumulation across tests within the same class.
     */
    private function resetConfigBinding(): void
    {
        $this->app->forgetInstance(Config::class);

        // Clear any leftover extenders from previous test runs.
        // The `extenders` property is a protected array on the container;
        // we use reflection to remove only the Config key.
        $ref = new \ReflectionProperty($this->app, 'extenders');
        $ref->setAccessible(true);
        $extenders = $ref->getValue($this->app);
        unset($extenders[Config::class]);
        $ref->setValue($this->app, $extenders);

        // Also unbind so the next scoped() starts fresh.
        $this->app->offsetUnset(Config::class);
    }

    #[Test]
    public function config_resolves_to_database_config_provider_when_spatie_registers_first(): void
    {
        $this->resetConfigBinding();
        $this->simulateSpatieRegisteredFirst();

        $config = $this->app->make(Config::class);

        $this->assertInstanceOf(
            DatabaseConfigProvider::class,
            $config,
            'Config should be a DatabaseConfigProvider when Spatie registers first and DB is available.'
        );
    }

    #[Test]
    public function config_resolves_to_database_config_provider_when_our_provider_registers_first(): void
    {
        $this->resetConfigBinding();
        $this->simulateOurProviderRegisteredFirst();

        $config = $this->app->make(Config::class);

        $this->assertInstanceOf(
            DatabaseConfigProvider::class,
            $config,
            'Extender must survive Spatie replacing the scoped binding after our provider.'
        );
    }

    #[Test]
    public function config_resolves_to_plain_config_when_database_is_not_setup(): void
    {
        $this->resetConfigBinding();
        $this->simulateOurProviderFirstWithNoDatabase();

        $config = $this->app->make(Config::class);

        $this->assertNotInstanceOf(
            DatabaseConfigProvider::class,
            $config,
            'Config should NOT be wrapped in DatabaseConfigProvider when DB tables are missing.'
        );

        $this->assertInstanceOf(
            Config::class,
            $config,
            'Config should fall back to the plain Spatie Config.'
        );

        // Also verify it's NOT a DatabaseConfigProvider by checking a
        // distinguishing property.  DatabaseConfigProvider overrides getBackup(),
        // so its backup property is a DatabaseBackupConfigProvider.
        $this->assertNotInstanceOf(
            DatabaseBackupConfigProvider::class,
            $config->backup,
            'Config->backup should be the plain BackupConfig, not the DB variant.'
        );
    }

    // ── Simulation helpers ────────────────────────────────────────────

    /**
     * Spatie's BackupServiceProvider registered its scoped binding,
     * then our ServiceProvider applies the extender.
     */
    private function simulateSpatieRegisteredFirst(): void
    {
        // Spatie: BackupServiceProvider::packageRegistered()
        $this->app->scoped(Config::class, fn (): Config => Config::fromArray(config('backup')));

        // Our provider: guard sees Config is bound → skips scoped, applies extender.
        $this->applyOurConfigExtender();
    }

    /**
     * Our ServiceProvider runs first (scoped fallback + extender),
     * then Spatie replaces the scoped binding.
     *
     * This is the critical ordering — the extender must survive the rebind.
     */
    private function simulateOurProviderRegisteredFirst(): void
    {
        // Our provider: Config not bound yet → scoped a fallback.
        if (! $this->app->bound(Config::class)) {
            $this->app->scoped(Config::class, fn (): Config => Config::fromArray(config('backup')));
        }

        $this->applyOurConfigExtender();

        // Spatie replaces the binding.
        $this->app->scoped(Config::class, fn (): Config => Config::fromArray(config('backup')));
    }

    /**
     * Our provider runs first, but the filesystem_configurations table
     * does not exist.  The extender should pass through the plain Config.
     */
    private function simulateOurProviderFirstWithNoDatabase(): void
    {
        if (! $this->app->bound(Config::class)) {
            $this->app->scoped(Config::class, fn (): Config => Config::fromArray(config('backup')));
        }

        // Extender that checks for a table known NOT to exist.
        $this->app->extend(Config::class, function (Config $config, Container $app): Config {
            if (Schema::hasTable('non_existent_table_xyz')) {
                return $app->make(DatabaseConfigProvider::class, ['original' => $config]);
            }

            return $config;
        });

        $this->app->scoped(Config::class, fn (): Config => Config::fromArray(config('backup')));
    }

    /**
     * The exact extender logic from ServiceProvider::rebindSpatieBackupConfig.
     */
    private function applyOurConfigExtender(): void
    {
        $this->app->extend(Config::class, function (Config $config, Container $app): Config {
            if (Schema::hasTable('filesystem_configurations')) {
                return $app->make(DatabaseConfigProvider::class, ['original' => $config]);
            }

            return $config;
        });
    }
}
