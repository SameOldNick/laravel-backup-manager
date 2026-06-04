<?php

namespace SameOldNick\BackupManager\Models\Factories;

use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Models\FilesystemConfigurationFTP;
use SameOldNick\BackupManager\Models\FilesystemConfigurationLocal;
use SameOldNick\BackupManager\Models\FilesystemConfigurationSFTP;
use Illuminate\Database\Eloquent\Factories\Factory;

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

    public function local(): static
    {
        return $this->state([
            'disk_type' => 'local',
        ])->configurable(FilesystemConfigurationLocal::factory());
    }

    public function ftp(): static
    {
        return $this->state([
            'disk_type' => 'ftp',
        ])->configurable(FilesystemConfigurationFTP::factory());
    }

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

    public function configurable($factory): static
    {
        return $this->afterMaking(function (FilesystemConfiguration $fsConfiguration) use ($factory) {
            $fsConfiguration->configurable()->associate($factory->create());
        });
    }
}
