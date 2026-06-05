<?php

namespace SameOldNick\BackupManager\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use SameOldNick\BackupManager\Models\Backup;

/**
 * @extends Factory<Backup>
 */
class BackupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = Backup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'error_message' => null,
        ];
    }

    public function successful($file = null): static
    {
        return $this->state(fn () => [
            'error_message' => null,
        ])->afterCreating(function (Backup $backup) use ($file) {
            // Ensure the backup file exists for successful backups
            $backup->file()->save($file ?? BackupFileFactory::new()->fakeFile()->create());
        });
    }

    public function failed($errorMessage = null): static
    {
        return $this->state(fn () => [
            'error_message' => value($errorMessage) ?? $this->faker->sentence(),
        ]);
    }

    public function fileNotFound($path = null): static
    {
        return $this->afterCreating(function (Backup $backup) use ($path) {
            // Create a backup with a file that doesn't exist to simulate file not found
            $backup->file()->save(BackupFileFactory::new()->missingFile($path)->create());
        });
    }

    public function deleted(): static
    {
        return $this->afterCreating(function (Backup $backup) {
            $backup->delete();
        });
    }
}
