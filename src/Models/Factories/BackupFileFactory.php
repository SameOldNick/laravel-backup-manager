<?php

namespace SameOldNick\BackupManager\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use SameOldNick\BackupManager\Models\BackupFile;

/**
 * @extends Factory<BackupFile>
 */
class BackupFileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = BackupFile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'created_at' => now(),
        ];
    }

    /**
     * Associate the backup file with a fileable model.
     *
     * @param  Factory|Model  $factory
     * @return BackupFileFactory
     */
    public function fileable($factory)
    {
        return $this->for(
            $factory, 'fileable'
        );
    }

    /**
     * Create a fake file and associate it with the backup file.
     *
     * @param  string|null  $name
     * @param  string  $disk
     * @param  int  $sizeInKilobytes
     * @return BackupFileFactory
     */
    public function fakeFile($name = null, $disk = 'local', $sizeInKilobytes = 100)
    {
        $uploadedFile = UploadedFile::fake()->create($name ?? $this->faker->word().'.txt', $sizeInKilobytes);

        return $this->uploadedFile($uploadedFile, '', $disk);
    }

    /**
     * Indicate that the backup file is missing by setting a path that doesn't exist on the disk.
     *
     * @param  string|null  $path
     * @return BackupFileFactory
     */
    public function missingFile($path = null)
    {
        return $this->state(fn () => [
            'path' => $path ?? $this->faker->word().'.txt',
        ]);
    }

    /**
     * Store the given uploaded file and associate it with the backup file.
     *
     * @param  UploadedFile|File|string|null  $uploadedFile
     * @param  string|null  $disk
     * @return BackupFileFactory
     */
    public function uploadedFile($uploadedFile, string $path = '', $disk = 'local')
    {
        return $this->state(fn () => [
            'path' => value($uploadedFile)->store($path, $disk ? ['disk' => $disk] : []),
        ]);
    }

    /**
     * Create a file with the given contents and associate it with the backup file.
     *
     * @param  string|null  $disk
     * @return BackupFileFactory
     */
    public function fromContents(string $fileName, string $contents, $disk = 'local')
    {
        return $this->state(function (array $attributes) use ($fileName, $contents, $disk) {
            $path = sprintf('files/%s', $fileName);

            Storage::disk($disk)->put($path, $contents);

            return [
                'path' => $path,
                'name' => null,
                'created_at' => Carbon::now(),
            ];
        });
    }
}
