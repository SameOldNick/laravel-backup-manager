<?php

namespace SameOldNick\BackupManager\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use SameOldNick\BackupManager\Models\BackupMonitor;

/**
 * @extends Factory<BackupMonitor>
 */
class BackupMonitorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = BackupMonitor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'maximum_age_in_days' => $this->faker->numberBetween(1, 365),
            'maximum_storage_in_megabytes' => $this->faker->numberBetween(1, 1024),
            'is_active' => $this->faker->boolean(),
        ];
    }
}
