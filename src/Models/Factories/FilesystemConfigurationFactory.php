<?php

namespace SameOldNick\BackupManager\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Models\FilesystemConfigurationFTP;
use SameOldNick\BackupManager\Models\FilesystemConfigurationLocal;
use SameOldNick\BackupManager\Models\FilesystemConfigurationSFTP;

/**
 * @extends Factory<FilesystemConfiguration>
 */
class FilesystemConfigurationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = FilesystemConfiguration::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word(),
            'slug' => $this->faker->unique()->slug(),
        ];
    }

    /**
     * Configure the filesystem as local.
     *
     * @return FilesystemConfigurationFactory
     */
    public function local(): static
    {
        return $this->state([
            'disk_type' => 'local',
        ])->configurable(FilesystemConfigurationLocal::factory());
    }

    /**
     * Configure the filesystem as FTP.
     *
     * @return FilesystemConfigurationFactory
     */
    public function ftp(): static
    {
        return $this->state([
            'disk_type' => 'ftp',
        ])->configurable(FilesystemConfigurationFTP::factory());
    }

    /**
     * Configure the filesystem as SFTP.
     *
     * @param  'password'|'key'|null  $authType
     * @return FilesystemConfigurationFactory
     */
    public function sftp(?string $authType = null): static
    {
        $factory = match ($authType) {
            'password' => FilesystemConfigurationSFTP::factory()->authPassword(),
            'key' => FilesystemConfigurationSFTP::factory()->authKey(),
            default => FilesystemConfigurationSFTP::factory(),
        };

        return $this->state([
            'disk_type' => 'sftp',
        ])->configurable($factory);
    }

    /**
     * Attach a polymorphic configuration model.
     *
     * @param  Factory<Model>  $factory
     * @return FilesystemConfigurationFactory
     */
    public function configurable($factory): static
    {
        return $this->afterMaking(function (FilesystemConfiguration $fsConfiguration) use ($factory) {
            $fsConfiguration->configurable()->associate($factory->create());
        });
    }
}
