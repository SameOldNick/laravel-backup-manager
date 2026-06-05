<?php

namespace SameOldNick\BackupManager\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use SameOldNick\BackupManager\Models\FilesystemConfigurationSFTP;

/**
 * @extends Factory<FilesystemConfigurationSFTP>
 */
class FilesystemConfigurationSFTPFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = FilesystemConfigurationSFTP::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'host' => $this->faker->boolean ? $this->faker->unique()->ipv4 : $this->faker->unique()->domainName,
            'port' => $this->faker->boolean(90) ? 22 : $this->faker->numberBetween(1000, 9999),
            'username' => $this->faker->unique()->userName,
            'extra' => $this->faker->boolean ? [] : null,
        ];
    }

    /**
     * Configure SFTP authentication with password credentials.
     *
     * @return FilesystemConfigurationSFTPFactory
     */
    public function authPassword()
    {
        return $this->state(fn () => [
            'password' => $this->faker->unique()->password,
        ]);
    }

    /**
     * Configure SFTP authentication with key credentials.
     *
     * @return FilesystemConfigurationSFTPFactory
     */
    public function authKey()
    {
        return $this->state(fn () => [
            'private_key' => $this->faker->unique()->sha256,
            'passphrase' => $this->faker->boolean ? $this->faker->unique()->password : null,
        ]);
    }
}
