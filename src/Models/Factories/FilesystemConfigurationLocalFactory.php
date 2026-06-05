<?php

namespace SameOldNick\BackupManager\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use SameOldNick\BackupManager\Models\FilesystemConfigurationLocal;

/**
 * @extends Factory<FilesystemConfigurationLocal>
 */
class FilesystemConfigurationLocalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = FilesystemConfigurationLocal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'root' => implode('/', $this->faker->words($this->faker->numberBetween(1, 4))),
            'extra' => $this->faker->boolean ? [] : null,
        ];
    }
}
