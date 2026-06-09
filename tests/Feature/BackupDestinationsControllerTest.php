<?php

namespace SameOldNick\BackupManager\Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Models\FilesystemConfigurationFTP;
use SameOldNick\BackupManager\Testing\Concerns;
use SameOldNick\BackupManager\Tests\TestCase;

class BackupDestinationsControllerTest extends TestCase
{
    use Concerns\UiResponderAssertions;

    public function test_displays_all_destinations(): void
    {
        $admin = $this->createAdmin();

        FilesystemConfiguration::factory()->local()->create(['is_active' => true]);
        FilesystemConfiguration::factory()->ftp()->create(['is_active' => false]);
        FilesystemConfiguration::factory()->sftp('key')->create(['is_active' => true]);

        $response = $this->actingAs($admin)->get(route('backup.destinations.index'));

        $response->assertOk();

        $this->assertResponderUsed($response, 'backup-destinations');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backupDestinations.data', 3),
            interacted: false
        );
    }

    public function test_displays_active_destinations(): void
    {
        $admin = $this->createAdmin();

        FilesystemConfiguration::factory()->local()->create(['is_active' => true]);
        FilesystemConfiguration::factory()->ftp()->create(['is_active' => false]);

        $response = $this->actingAs($admin)->get(route('backup.destinations.index', [
            'status' => 'enabled',
        ]));

        $response->assertOk();

        $this->assertResponderUsed($response, 'backup-destinations');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backupDestinations.data', 1),
            interacted: false
        );
    }

    public function test_displays_inactive_destinations(): void
    {
        $admin = $this->createAdmin();

        FilesystemConfiguration::factory()->local()->create(['is_active' => true]);
        FilesystemConfiguration::factory()->ftp()->create(['is_active' => false]);

        $response = $this->actingAs($admin)->get(route('backup.destinations.index', [
            'status' => 'disabled',
        ]));

        $response->assertOk();

        $this->assertResponderUsed($response, 'backup-destinations');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backupDestinations.data', 1),
            interacted: false
        );
    }

    public function test_displays_destinations_filtered_by_query(): void
    {
        $admin = $this->createAdmin();

        FilesystemConfiguration::factory()->local()->create([
            'name' => 'Primary Archive',
            'is_active' => true,
        ]);

        FilesystemConfiguration::factory()->ftp()->create([
            'name' => 'Secondary Archive',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('backup.destinations.index', [
            'query' => 'Primary',
        ]));

        $response->assertOk();

        $this->assertResponderUsed($response, 'backup-destinations');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backupDestinations.data', 1),
            interacted: false
        );
    }

    public function test_creates_local_destination(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('backup.destinations.store'), [
            'enabled' => true,
            'name' => 'Local Archive',
            'slug' => 'local-archive',
            'type' => 'local',
            'root' => 'backups/local',
            'extra' => ['visibility' => 'private'],
        ]);

        $response->assertOk();

        $this->assertResponderUsed($response, 'backup-destinations');
        $this->assertResponseId($response, 'store');

        $destination = FilesystemConfiguration::query()->where('name', 'Local Archive')->firstOrFail();

        $this->assertSame('local', $destination->disk_type);
        $this->assertTrue((bool) $destination->is_active);
        $this->assertSame('local-archive', $destination->slug);
        $this->assertSame('backups/local', $destination->configurable->root);
    }

    public function test_creates_ftp_destination(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('backup.destinations.store'), [
            'enabled' => true,
            'name' => 'FTP Archive',
            'type' => 'ftp',
            'host' => 'ftp.example.com',
            'port' => 21,
            'username' => 'ftp-user',
            'password' => 'ftp-password',
            'root' => 'backups/ftp',
        ]);

        $response->assertOk();

        $this->assertResponderUsed($response, 'backup-destinations');
        $this->assertResponseId($response, 'store');

        $destination = FilesystemConfiguration::query()->where('name', 'FTP Archive')->firstOrFail();

        $this->assertSame('ftp', $destination->disk_type);
        $this->assertTrue((bool) $destination->is_active);
        $this->assertInstanceOf(FilesystemConfigurationFTP::class, $destination->configurable);
        $this->assertSame('ftp-password', $destination->configurable->password);
    }

    public function test_creates_sftp_destination_with_username_and_password(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('backup.destinations.store'), [
            'enabled' => true,
            'name' => 'SFTP Password Archive',
            'type' => 'sftp',
            'auth_type' => 'password',
            'host' => 'sftp.example.com',
            'port' => 22,
            'username' => 'sftp-user',
            'password' => 'sftp-password',
            'root' => 'backups/sftp-password',
        ]);

        $response->assertOk();

        $destination = FilesystemConfiguration::query()->where('name', 'SFTP Password Archive')->firstOrFail();

        $this->assertSame('sftp', $destination->disk_type);
        $this->assertSame('sftp-password', $destination->configurable->password);
        $this->assertNull($destination->configurable->private_key);
        $this->assertNull($destination->configurable->passphrase);
    }

    public function test_creates_sftp_destination_with_private_key(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('backup.destinations.store'), [
            'enabled' => true,
            'name' => 'SFTP Key Archive',
            'type' => 'sftp',
            'auth_type' => 'key',
            'host' => 'sftp.example.com',
            'port' => 22,
            'username' => 'sftp-user',
            'private_key' => 'private-key-value',
            'root' => 'backups/sftp-key',
        ]);

        $response->assertOk();

        $destination = FilesystemConfiguration::query()->where('name', 'SFTP Key Archive')->firstOrFail();

        $this->assertSame('sftp', $destination->disk_type);
        $this->assertSame('private-key-value', $destination->configurable->private_key);
        $this->assertNull($destination->configurable->passphrase);
        $this->assertNull($destination->configurable->password);
    }

    public function test_creates_sftp_destination_with_private_key_and_passphrase(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('backup.destinations.store'), [
            'enabled' => true,
            'name' => 'SFTP Key Passphrase Archive',
            'type' => 'sftp',
            'auth_type' => 'key',
            'host' => 'sftp.example.com',
            'port' => 22,
            'username' => 'sftp-user',
            'private_key' => 'private-key-value',
            'passphrase' => 'passphrase-value',
            'root' => 'backups/sftp-key-passphrase',
        ]);

        $response->assertOk();

        $destination = FilesystemConfiguration::query()->where('name', 'SFTP Key Passphrase Archive')->firstOrFail();

        $this->assertSame('sftp', $destination->disk_type);
        $this->assertSame('private-key-value', $destination->configurable->private_key);
        $this->assertSame('passphrase-value', $destination->configurable->passphrase);
    }

    public function test_updates_destination_name_and_slug(): void
    {
        $admin = $this->createAdmin();

        $destination = FilesystemConfiguration::factory()->local()->create([
            'name' => 'Old Name',
            'slug' => 'old-name',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('backup.destinations.update', $destination), [
            'name' => 'New Name',
            'slug' => 'new-name',
        ]);

        $response->assertOk();

        $destination->refresh();

        $this->assertSame('New Name', $destination->name);
        $this->assertSame('new-name', $destination->slug);
    }

    public function test_updates_ftp_destination_password(): void
    {
        $admin = $this->createAdmin();

        $destination = FilesystemConfiguration::factory()->ftp()->create([
            'name' => 'FTP Update',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('backup.destinations.update', $destination), [
            'auth_type' => 'password',
            'password' => 'updated-ftp-password',
        ]);

        $response->assertOk();

        $destination->refresh();

        $this->assertSame('updated-ftp-password', $destination->configurable->password);
    }

    public function test_updates_sftp_destination_password(): void
    {
        $admin = $this->createAdmin();

        $destination = FilesystemConfiguration::factory()->sftp('password')->create([
            'name' => 'SFTP Password Update',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('backup.destinations.update', $destination), [
            'auth_type' => 'password',
            'password' => 'updated-sftp-password',
        ]);

        $response->assertOk();

        $destination->refresh();

        $this->assertSame('updated-sftp-password', $destination->configurable->password);
        $this->assertNull($destination->configurable->private_key);
    }

    public function test_updates_sftp_destination_private_key_and_passphrase(): void
    {
        $admin = $this->createAdmin();

        $destination = FilesystemConfiguration::factory()->sftp('key')->create([
            'name' => 'SFTP Key Update',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('backup.destinations.update', $destination), [
            'auth_type' => 'key',
            'private_key' => 'updated-private-key',
            'passphrase' => 'updated-passphrase',
        ]);

        $response->assertOk();

        $destination->refresh();

        $this->assertSame('updated-private-key', $destination->configurable->private_key);
        $this->assertSame('updated-passphrase', $destination->configurable->passphrase);
        $this->assertNull($destination->configurable->password);
    }

    public function test_updates_sftp_destination_from_password_auth_to_private_key_auth(): void
    {
        $admin = $this->createAdmin();

        $destination = FilesystemConfiguration::factory()->sftp('password')->create([
            'name' => 'SFTP Auth Switch',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('backup.destinations.update', $destination), [
            'auth_type' => 'key',
            'private_key' => 'switched-private-key',
            'passphrase' => 'switched-passphrase',
        ]);

        $response->assertOk();

        $destination->refresh();

        $this->assertNull($destination->configurable->password);
        $this->assertSame('switched-private-key', $destination->configurable->private_key);
        $this->assertSame('switched-passphrase', $destination->configurable->passphrase);
    }

    public function test_removes_destination(): void
    {
        $admin = $this->createAdmin();

        $destination = FilesystemConfiguration::factory()->local()->create([
            'name' => 'Delete Me',
            'is_active' => true,
        ]);

        $configurable = $destination->configurable;

        $response = $this->actingAs($admin)->delete(route('backup.destinations.destroy', $destination));

        $response->assertOk();

        $this->assertResponderUsed($response, 'backup-destinations');
        $this->assertResponseId($response, 'destroy');

        $this->assertDatabaseMissing('filesystem_configurations', [
            'id' => $destination->id,
        ]);

        $this->assertDatabaseMissing($configurable->getTable(), [
            'id' => $configurable->id,
        ]);
    }
}
