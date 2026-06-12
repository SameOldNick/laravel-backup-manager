<?php

namespace SameOldNick\BackupManager\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use SameOldNick\BackupManager\Enums\RunStatus;
use SameOldNick\BackupManager\Models\BackupDestinationTestRun;

/**
 * @extends Factory<BackupDestinationTestRun>
 */
class BackupDestinationTestRunFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = BackupDestinationTestRun::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'status' => RunStatus::Pending->value,
        ];
    }
}
