<?php

namespace SameOldNick\BackupManager\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
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

    /**
     * Indicate that the backup was successful.
     *
     * @param  UploadedFile|File|string|null  $file
     * @return BackupFactory
     */
    public function successful($file = null): static
    {
        return $this->state(fn () => [
            'error_message' => null,
        ])->afterCreating(function (Backup $backup) use ($file) {
            // Ensure the backup file exists for successful backups
            $backup->file()->save($file ?? BackupFileFactory::new()->fakeFile()->create());
        });
    }

    /**
     * Indicate that the backup failed with an error message.
     *
     * @param  string|null  $errorMessage
     * @return BackupFactory
     */
    public function failed($errorMessage = null): static
    {
        return $this->state(fn () => [
            'error_message' => value($errorMessage) ?? $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the backup file is missing to simulate a file not found scenario.
     *
     * @param  string|null  $path
     * @return BackupFactory
     */
    public function fileNotFound($path = null): static
    {
        return $this->afterCreating(function (Backup $backup) use ($path) {
            // Create a backup with a file that doesn't exist to simulate file not found
            $backup->file()->save(BackupFileFactory::new()->missingFile($path)->create());
        });
    }

    /**
     * Indicate that the backup was deleted.
     *
     * @return BackupFactory
     */
    public function deleted(): static
    {
        return $this->afterCreating(function (Backup $backup) {
            $backup->delete();
        });
    }
}
