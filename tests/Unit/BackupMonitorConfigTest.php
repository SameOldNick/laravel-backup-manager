<?php

namespace SameOldNick\BackupManager\Tests\Unit;

use SameOldNick\BackupManager\DeferredServiceProvider;
use SameOldNick\BackupManager\Models\BackupMonitor;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Testing\Concerns;
use SameOldNick\BackupManager\Tests\TestCase;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes;

class BackupMonitorConfigTest extends TestCase
{
    use Concerns\MonitorTestHelpers;

    public function test_monitored_backups_fallback_from_config_file(): void
    {
        config([
            'backup-manager.config_fallbacks.monitor_backups' => true,
        ]);

        $this->app->forgetInstance(Config::class);
        $this->app->register(DeferredServiceProvider::class);

        $this->assertMonitoredBackups(function (array $monitors) {
            $this->assertCount(1, $monitors);
            $this->assertSame('Laravel', $monitors[0]['name']);
            $this->assertCount(1, $monitors[0]['disks']);
            $this->assertCount(2, $monitors[0]['healthChecks']);
        });
    }

    public function test_monitored_backups_doesnt_fallback_from_config_file(): void
    {
        config([
            'backup-manager.config_fallbacks.monitor_backups' => false,
        ]);

        $this->app->forgetInstance(Config::class);
        $this->app->register(DeferredServiceProvider::class);

        $this->assertMonitoredBackups(function (array $monitors) {
            $this->assertEmpty($monitors);
        });
    }

    public function test_monitored_backups_loaded_from_db_without_disks(): void
    {
        $monitor = BackupMonitor::factory()->create([
            'is_active' => true,
        ]);

        $this->app->forgetInstance(Config::class);
        $this->app->register(DeferredServiceProvider::class);

        $this->assertMonitoredBackups(function (array $monitors) use ($monitor) {
            $this->assertCount(1, $monitors);
            $this->assertSame($monitor->name, $monitors[0]['name']);
            $this->assertEmpty($monitors[0]['disks']);
        });
    }

    public function test_monitored_backups_loaded_from_db_with_active_disk(): void
    {
        $fsConfig = FilesystemConfiguration::factory()->local()->create([
            'name' => 'test-disk',
            'slug' => 'test-disk',
            'is_active' => true,
        ]);

        $fsConfig->refresh();

        $monitor = BackupMonitor::factory()->create([
            'is_active' => true,
        ]);

        $monitor->filesystemConfigurations()->attach($fsConfig);

        $this->app->forgetInstance(Config::class);
        $this->app->register(DeferredServiceProvider::class);

        $this->assertMonitoredBackups(function (array $monitors) use ($monitor) {
            $this->assertCount(1, $monitors);
            $this->assertSame($monitor->name, $monitors[0]['name']);
            $this->assertCount(1, $monitors[0]['disks']);
            $this->assertNotEmpty($monitors[0]['disks'][0]);
            $this->assertSame('dynamic-test-disk', $monitors[0]['disks'][0]);
        });
    }

    public function test_monitored_backups_loaded_from_db_with_inactive_disk(): void
    {
        $fsConfig1 = FilesystemConfiguration::factory()->local()->create([
            'name' => 'test-disk-1',
            'slug' => 'test-disk-1',
            'is_active' => true,
        ]);

        $fsConfig2 = FilesystemConfiguration::factory()->local()->create([
            'name' => 'test-disk-2',
            'slug' => 'test-disk-2',
            'is_active' => false,
        ]);

        $monitor = BackupMonitor::factory()->create([
            'is_active' => true,
        ]);

        $monitor->filesystemConfigurations()->sync([$fsConfig1, $fsConfig2]);

        // Clear the config instance
        $this->app->forgetInstance(Config::class);
        $this->app->register(DeferredServiceProvider::class);

        $this->assertMonitoredBackups(function (array $monitors) use ($monitor) {
            $this->assertCount(1, $monitors);
            $this->assertSame($monitor->name, $monitors[0]['name']);
            $this->assertCount(1, $monitors[0]['disks']);
            $this->assertNotEmpty($monitors[0]['disks'][0]);
            $this->assertSame('dynamic-test-disk-1', $monitors[0]['disks'][0]);
        });
    }

    public function test_monitored_backups_no_health_checks(): void
    {
        $fsConfig = FilesystemConfiguration::factory()->local()->create([
            'name' => 'test-disk',
            'slug' => 'test-disk',
            'is_active' => true,
        ]);

        $fsConfig->refresh();

        $monitor = BackupMonitor::factory()->create([
            'maximum_age_in_days' => null,
            'maximum_storage_in_megabytes' => null,
            'is_active' => true,
        ]);

        $monitor->filesystemConfigurations()->attach($fsConfig);

        $this->app->forgetInstance(Config::class);
        $this->app->register(DeferredServiceProvider::class);

        $this->assertMonitoredBackups(function (array $monitors) use ($monitor) {
            $this->assertCount(1, $monitors);
            $this->assertSame($monitor->name, $monitors[0]['name']);
            $this->assertEmpty($monitors[0]['healthChecks']);
        });
    }

    public function test_monitored_backups_has_max_age_health_check(): void
    {
        $fsConfig = FilesystemConfiguration::factory()->local()->create([
            'name' => 'test-disk',
            'slug' => 'test-disk',
            'is_active' => true,
        ]);

        $fsConfig->refresh();

        $monitor = BackupMonitor::factory()->create([
            'maximum_age_in_days' => 5,
            'maximum_storage_in_megabytes' => null,
            'is_active' => true,
        ]);

        $monitor->filesystemConfigurations()->attach($fsConfig);

        $this->app->forgetInstance(Config::class);
        $this->app->register(DeferredServiceProvider::class);

        $this->assertMonitoredBackups(function (array $monitors) use ($monitor) {
            $this->assertCount(1, $monitors);
            $this->assertSame($monitor->name, $monitors[0]['name']);
            $this->assertCount(1, $monitors[0]['healthChecks']);
            $this->assertArrayHasKey(MaximumAgeInDays::class, $monitors[0]['healthChecks']);
            $this->assertSame(5, $monitors[0]['healthChecks'][MaximumAgeInDays::class]);
        });
    }

    public function test_monitored_backups_has_max_storage_health_check(): void
    {
        $fsConfig = FilesystemConfiguration::factory()->local()->create([
            'name' => 'test-disk',
            'slug' => 'test-disk',
            'is_active' => true,
        ]);

        $fsConfig->refresh();

        $monitor = BackupMonitor::factory()->create([
            'maximum_age_in_days' => null,
            'maximum_storage_in_megabytes' => 100,
            'is_active' => true,
        ]);

        $monitor->filesystemConfigurations()->attach($fsConfig);

        $this->app->forgetInstance(Config::class);
        $this->app->register(DeferredServiceProvider::class);

        $this->assertMonitoredBackups(function (array $monitors) use ($monitor) {
            $this->assertCount(1, $monitors);
            $this->assertSame($monitor->name, $monitors[0]['name']);
            $this->assertCount(1, $monitors[0]['healthChecks']);
            $this->assertArrayHasKey(MaximumStorageInMegabytes::class, $monitors[0]['healthChecks']);
            $this->assertSame(100, $monitors[0]['healthChecks'][MaximumStorageInMegabytes::class]);
        });
    }

    public function test_monitored_backups_has_all_health_checks(): void
    {
        $fsConfig = FilesystemConfiguration::factory()->local()->create([
            'name' => 'test-disk',
            'slug' => 'test-disk',
            'is_active' => true,
        ]);

        $fsConfig->refresh();

        $monitor = BackupMonitor::factory()->create([
            'maximum_age_in_days' => 5,
            'maximum_storage_in_megabytes' => 100,
            'is_active' => true,
        ]);

        $monitor->filesystemConfigurations()->attach($fsConfig);

        $this->app->forgetInstance(Config::class);
        $this->app->register(DeferredServiceProvider::class);

        $this->assertMonitoredBackups(function (array $monitors) use ($monitor) {
            $this->assertCount(1, $monitors);
            $this->assertSame($monitor->name, $monitors[0]['name']);
            $this->assertCount(2, $monitors[0]['healthChecks']);
            $this->assertArrayHasKey(MaximumAgeInDays::class, $monitors[0]['healthChecks']);
            $this->assertSame(5, $monitors[0]['healthChecks'][MaximumAgeInDays::class]);
            $this->assertArrayHasKey(MaximumStorageInMegabytes::class, $monitors[0]['healthChecks']);
            $this->assertSame(100, $monitors[0]['healthChecks'][MaximumStorageInMegabytes::class]);
        });
    }
}
