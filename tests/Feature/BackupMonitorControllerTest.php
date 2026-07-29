<?php

namespace SameOldNick\BackupManager\Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;
use SameOldNick\BackupManager\Models\BackupMonitor;
use SameOldNick\BackupManager\Testing\Concerns;
use SameOldNick\BackupManager\Tests\TestCase;

class BackupMonitorControllerTest extends TestCase
{
    use Concerns\UiResponderAssertions;

    public function test_displays_all_monitors(): void
    {
        $admin = $this->createAdmin();

        BackupMonitor::query()->create([
            'name' => 'Daily Monitor',
            'disks' => ['local'],
            'maximum_age_in_days' => 7,
            'maximum_storage_in_megabytes' => 1024,
            'is_active' => true,
        ]);

        BackupMonitor::query()->create([
            'name' => 'Weekly Monitor',
            'disks' => ['s3'],
            'maximum_age_in_days' => 14,
            'maximum_storage_in_megabytes' => 2048,
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('backup.monitors.index'));

        $response->assertOk();

        $this->assertResponderUsed($response, 'backup-monitors');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backupMonitors.data', 2),
            interacted: false
        );
    }

    public function test_displays_only_active_monitors(): void
    {
        $admin = $this->createAdmin();

        BackupMonitor::query()->create([
            'name' => 'Active Monitor',
            'disks' => ['local'],
            'maximum_age_in_days' => 3,
            'maximum_storage_in_megabytes' => 512,
            'is_active' => true,
        ]);

        BackupMonitor::query()->create([
            'name' => 'Inactive Monitor',
            'disks' => ['s3'],
            'maximum_age_in_days' => 3,
            'maximum_storage_in_megabytes' => 512,
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('backup.monitors.index', [
            'active' => 1,
        ]));

        $response->assertOk();

        $this->assertResponderUsed($response, 'backup-monitors');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backupMonitors.data', 1),
            interacted: false
        );
    }

    public function test_displays_only_inactive_monitors(): void
    {
        $admin = $this->createAdmin();

        BackupMonitor::query()->create([
            'name' => 'Active Monitor',
            'disks' => ['local'],
            'maximum_age_in_days' => 3,
            'maximum_storage_in_megabytes' => 512,
            'is_active' => true,
        ]);

        BackupMonitor::query()->create([
            'name' => 'Inactive Monitor',
            'disks' => ['s3'],
            'maximum_age_in_days' => 3,
            'maximum_storage_in_megabytes' => 512,
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('backup.monitors.index', [
            'active' => 0,
        ]));

        $response->assertOk();

        $this->assertResponderUsed($response, 'backup-monitors');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backupMonitors.data', 1),
            interacted: false
        );
    }

    public function test_displays_monitors_filtered_by_query(): void
    {
        $admin = $this->createAdmin();

        BackupMonitor::query()->create([
            'name' => 'Primary Monitor',
            'disks' => ['local'],
            'maximum_age_in_days' => 7,
            'maximum_storage_in_megabytes' => 1024,
            'is_active' => true,
        ]);

        BackupMonitor::query()->create([
            'name' => 'Secondary Monitor',
            'disks' => ['s3'],
            'maximum_age_in_days' => 7,
            'maximum_storage_in_megabytes' => 1024,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('backup.monitors.index', [
            'query' => 'Primary',
        ]));

        $response->assertOk();

        $this->assertResponderUsed($response, 'backup-monitors');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backupMonitors.data', 1),
            interacted: false
        );
    }

    public function test_displays_create_monitor_form(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('backup.monitors.create'));

        $response->assertOk();

        $this->assertResponderUsed($response, 'backup-monitors');
        $this->assertResponseId($response, 'create');
    }

    public function test_creates_monitor(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('backup.monitors.store'), [
            'name' => 'Local Monitor',
            'disks' => ['local', 's3'],
            'maximum_age_in_days' => 5,
            'maximum_storage_in_megabytes' => 2048,
            'enabled' => true,
        ]);

        $response->assertOk();

        $this->assertResponderUsed($response, 'backup-monitors');
        $this->assertResponseId($response, 'store');

        $monitor = BackupMonitor::query()->where('name', 'Local Monitor')->firstOrFail();

        $this->assertSame(['local', 's3'], $monitor->disks);
        $this->assertSame(5, $monitor->maximum_age_in_days);
        $this->assertSame(2048, $monitor->maximum_storage_in_megabytes);
        $this->assertTrue($monitor->is_active);
    }

    public function test_creates_monitor_as_enabled_when_enabled_field_is_missing(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('backup.monitors.store'), [
            'name' => 'Default Enabled Monitor',
            'disks' => ['local'],
            'maximum_age_in_days' => 10,
            'maximum_storage_in_megabytes' => 1024,
        ]);

        $response->assertOk();

        $monitor = BackupMonitor::query()->where('name', 'Default Enabled Monitor')->firstOrFail();

        $this->assertTrue($monitor->is_active);
    }

    public function test_displays_edit_monitor_form(): void
    {
        $admin = $this->createAdmin();

        $monitor = BackupMonitor::query()->create([
            'name' => 'Edit Me',
            'disks' => ['local'],
            'maximum_age_in_days' => 2,
            'maximum_storage_in_megabytes' => 256,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('backup.monitors.edit', $monitor));

        $response->assertOk();

        $this->assertResponderUsed($response, 'backup-monitors');
        $this->assertResponseId($response, 'edit');
    }

    public function test_updates_monitor_fields_and_disables_monitor(): void
    {
        $admin = $this->createAdmin();

        $monitor = BackupMonitor::query()->create([
            'name' => 'Old Monitor Name',
            'disks' => ['local'],
            'maximum_age_in_days' => 7,
            'maximum_storage_in_megabytes' => 512,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('backup.monitors.update', $monitor), [
            'name' => 'Updated Monitor Name',
            'disks' => ['s3'],
            'maximum_age_in_days' => 14,
            'maximum_storage_in_megabytes' => 4096,
            'enabled' => false,
        ]);

        $response->assertOk();

        $this->assertResponderUsed($response, 'backup-monitors');
        $this->assertResponseId($response, 'update');

        $monitor->refresh();

        $this->assertSame('Updated Monitor Name', $monitor->name);
        $this->assertSame(['s3'], $monitor->disks);
        $this->assertSame(14, $monitor->maximum_age_in_days);
        $this->assertSame(4096, $monitor->maximum_storage_in_megabytes);
        $this->assertFalse($monitor->is_active);
    }

    public function test_updates_monitor_without_enabled_field_keeps_existing_enabled_state(): void
    {
        $admin = $this->createAdmin();

        $monitor = BackupMonitor::query()->create([
            'name' => 'Keep Enabled State',
            'disks' => ['local'],
            'maximum_age_in_days' => 7,
            'maximum_storage_in_megabytes' => 512,
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->put(route('backup.monitors.update', $monitor), [
            'name' => 'Still Disabled',
        ]);

        $response->assertOk();

        $monitor->refresh();

        $this->assertSame('Still Disabled', $monitor->name);
        $this->assertFalse($monitor->is_active);
    }

    public function test_removes_monitor(): void
    {
        $admin = $this->createAdmin();

        $monitor = BackupMonitor::query()->create([
            'name' => 'Delete Me',
            'disks' => ['local'],
            'maximum_age_in_days' => 7,
            'maximum_storage_in_megabytes' => 512,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('backup.monitors.destroy', $monitor));

        $response->assertOk();

        $this->assertResponderUsed($response, 'backup-monitors');
        $this->assertResponseId($response, 'destroy');

        $this->assertModelMissing($monitor);
    }
}
