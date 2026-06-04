<?php

namespace SameOldNick\BackupManager\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
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

    public function fileable($factory)
    {
        return $this->for(
            $factory, 'fileable'
        );
    }

    public function fakeFile($name = null, $disk = 'local', $sizeInKilobytes = 100)
    {
        $uploadedFile = UploadedFile::fake()->create($name ?? $this->faker->word().'.txt', $sizeInKilobytes);

        return $this->uploadedFile($uploadedFile, '', $disk);
    }

    public function missingFile($path = null)
    {
        return $this->state(fn () => [
            'path' => $path ?? $this->faker->word().'.txt',
        ]);
    }

    public function uploadedFile($uploadedFile, string $path = '', $disk = 'local')
    {
        return $this->state(fn () => [
            'path' => value($uploadedFile)->store($path, $disk ? ['disk' => $disk] : []),
        ]);
    }

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
