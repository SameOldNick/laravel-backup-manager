<?php

namespace SameOldNick\BackupManager\Tests\Unit;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\PhpseclibV3\SftpAdapter;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Models\FilesystemConfigurationFTP;
use SameOldNick\BackupManager\Models\FilesystemConfigurationSFTP;
use SameOldNick\BackupManager\Tests\TestCase;

/**
 * Covers dynamic disk resolution and generated filesystem config values.
 */
class FilesystemConfigurationTest extends TestCase
{
    /**
     * Ensures disabled local configurations cannot be resolved as disks.
     */
    public function test_dynamic_local_disk_cannot_resolve_when_configuration_is_disabled(): void
    {
        $fsConfig = FilesystemConfiguration::factory()->local()->create([
            'is_active' => false,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        /** @var Illuminate\Filesystem\FilesystemAdapter */
        Storage::disk("dynamic-{$fsConfig->slug}");
    }

    /**
     * Ensures enabled local configurations resolve to the local adapter.
     */
    public function test_dynamic_local_disk_resolves_when_configuration_is_enabled(): void
    {
        $fsConfig = FilesystemConfiguration::factory()->local()->create([
            'is_active' => true,
        ]);

        /** @var Illuminate\Filesystem\FilesystemAdapter */
        $disk = Storage::disk("dynamic-{$fsConfig->slug}");

        $this->assertInstanceOf(FilesystemAdapter::class, $disk);
        $this->assertInstanceOf(LocalFilesystemAdapter::class, $disk->getAdapter());
    }

    /**
     * Verifies SFTP disk configuration is generated from persisted settings.
     */
    public function test_sftp_driver_config_is_generated_from_persisted_configuration(): void
    {
        $sftpConfig = FilesystemConfigurationSFTP::create([
            'host' => 'sftp.example.com',
            'port' => 22,
            'username' => 'user',
            'password' => 'password',
            'private_key' => 'private_key',
            'passphrase' => 'passphrase',
            'root' => '/',
            'extra' => [],
        ]);

        $fsConfig = FilesystemConfiguration::factory()->create([
            'name' => 'Test SFTP Config',
            'disk_type' => 'sftp',
            'is_active' => true,
            'configurable_type' => $sftpConfig->getMorphClass(),
            'configurable_id' => $sftpConfig->id,
        ]);

        $config = $fsConfig->getFilesystemConfig();

        $this->assertEquals('sftp', $config['driver']);
        $this->assertEquals('sftp.example.com', $config['host']);
        $this->assertEquals(22, $config['port']);
        $this->assertEquals('user', $config['username']);
        $this->assertEquals('password', $config['password']);
        $this->assertEquals('private_key', $config['privateKey']);
        $this->assertEquals('passphrase', $config['passphrase']);
        $this->assertEquals('/', $config['root']);
    }

    /**
     * Resolves SFTP disk using an explicit slug and validates adapter/config.
     */
    public function test_dynamic_sftp_disk_resolves_by_explicit_slug(): void
    {
        $sftpConfig = FilesystemConfigurationSFTP::factory()->authKey()->create();

        $fsConfig = FilesystemConfiguration::factory()->create([
            'name' => 'Test SFTP Config',
            'slug' => 'test-sftp-config',
            'disk_type' => 'sftp',
            'is_active' => true,
            'configurable_type' => $sftpConfig->getMorphClass(),
            'configurable_id' => $sftpConfig->id,
        ]);

        /** @var Illuminate\Filesystem\FilesystemAdapter */
        $disk = Storage::disk('dynamic-test-sftp-config');

        $this->assertInstanceOf(FilesystemAdapter::class, $disk);
        $this->assertInstanceOf(SftpAdapter::class, $disk->getAdapter());

        $config = $disk->getConfig();

        $this->assertEquals($sftpConfig->host, $config['host']);
        $this->assertEquals($sftpConfig->port, $config['port']);
        $this->assertEquals($sftpConfig->username, $config['username']);
        $this->assertEquals($sftpConfig->private_key, $config['privateKey']);
        $this->assertEquals($sftpConfig->passphrase, $config['passphrase']);
    }

    /**
     * Resolves SFTP disk using generated slug and validates adapter/config.
     */
    public function test_dynamic_sftp_disk_resolves_by_generated_slug(): void
    {
        $sftpConfig = FilesystemConfigurationSFTP::factory()->authKey()->create();

        $fsConfig = FilesystemConfiguration::factory()->create([
            'disk_type' => 'sftp',
            'is_active' => true,
            'configurable_type' => $sftpConfig->getMorphClass(),
            'configurable_id' => $sftpConfig->id,
        ]);

        /** @var Illuminate\Filesystem\FilesystemAdapter */
        $disk = Storage::disk("dynamic-{$fsConfig->slug}");

        $this->assertInstanceOf(FilesystemAdapter::class, $disk);
        $this->assertInstanceOf(SftpAdapter::class, $disk->getAdapter());

        $config = $disk->getConfig();

        $this->assertEquals($sftpConfig->host, $config['host']);
        $this->assertEquals($sftpConfig->port, $config['port']);
        $this->assertEquals($sftpConfig->username, $config['username']);
        $this->assertEquals($sftpConfig->private_key, $config['privateKey']);
        $this->assertEquals($sftpConfig->passphrase, $config['passphrase']);
    }

    /**
     * Verifies FTP disk configuration is generated from persisted settings.
     */
    public function test_ftp_driver_config_is_generated_from_persisted_configuration(): void
    {
        $sftpConfig = FilesystemConfigurationFTP::create([
            'host' => 'ftp.example.com',
            'port' => 21,
            'username' => 'user',
            'password' => 'password',
            'root' => '/',
            'extra' => [],
        ]);

        $fsConfig = FilesystemConfiguration::factory()->create([
            'name' => 'Test FTP Config',
            'disk_type' => 'ftp',
            'is_active' => true,
            'configurable_type' => $sftpConfig->getMorphClass(),
            'configurable_id' => $sftpConfig->id,
        ]);

        $config = $fsConfig->getFilesystemConfig();

        $this->assertEquals('ftp', $config['driver']);
        $this->assertEquals('ftp.example.com', $config['host']);
        $this->assertEquals(21, $config['port']);
        $this->assertEquals('user', $config['username']);
        $this->assertEquals('password', $config['password']);
        $this->assertEquals('/', $config['root']);
    }

    /**
     * Resolves FTP disk using an explicit slug and validates adapter/config.
     */
    public function test_dynamic_ftp_disk_resolves_by_explicit_slug(): void
    {
        $sftpConfig = FilesystemConfigurationFTP::factory()->create();

        $fsConfig = FilesystemConfiguration::factory()->create([
            'name' => 'Test FTP Config',
            'slug' => 'test-ftp-config',
            'disk_type' => 'ftp',
            'is_active' => true,
            'configurable_type' => $sftpConfig->getMorphClass(),
            'configurable_id' => $sftpConfig->id,
        ]);

        /** @var Illuminate\Filesystem\FilesystemAdapter */
        $disk = Storage::disk('dynamic-test-ftp-config');

        $this->assertInstanceOf(FilesystemAdapter::class, $disk);
        $this->assertInstanceOf(FtpAdapter::class, $disk->getAdapter());

        $config = $disk->getConfig();

        $this->assertEquals($sftpConfig->host, $config['host']);
        $this->assertEquals($sftpConfig->port, $config['port']);
        $this->assertEquals($sftpConfig->username, $config['username']);
        $this->assertEquals($sftpConfig->password, $config['password']);
        $this->assertEquals($sftpConfig->root, $config['root']);
    }

    /**
     * Resolves FTP disk using generated slug and validates adapter/config.
     */
    public function test_dynamic_ftp_disk_resolves_by_generated_slug(): void
    {
        $sftpConfig = FilesystemConfigurationFTP::factory()->create();

        $fsConfig = FilesystemConfiguration::factory()->create([
            'disk_type' => 'ftp',
            'is_active' => true,
            'configurable_type' => $sftpConfig->getMorphClass(),
            'configurable_id' => $sftpConfig->id,
        ]);

        /** @var Illuminate\Filesystem\FilesystemAdapter */
        $disk = Storage::disk("dynamic-{$fsConfig->slug}");

        $this->assertInstanceOf(FilesystemAdapter::class, $disk);
        $this->assertInstanceOf(FtpAdapter::class, $disk->getAdapter());

        $config = $disk->getConfig();

        $this->assertEquals($sftpConfig->host, $config['host']);
        $this->assertEquals($sftpConfig->port, $config['port']);
        $this->assertEquals($sftpConfig->username, $config['username']);
        $this->assertEquals($sftpConfig->password, $config['password']);
        $this->assertEquals($sftpConfig->root, $config['root']);
    }
}
