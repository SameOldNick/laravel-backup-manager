<?php

namespace SameOldNick\BackupManager\Models;

use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use SameOldNick\BackupManager\Models\Factories\BackupFileFactory;

/**
 * @property string $id
 * @property string $name
 * @property string $path
 * @property string $disk
 * @property bool $is_public
 * @property ?int $user_id
 * @property ?string $fileable_type
 * @property ?string $fileable_id
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property ?Carbon $deleted_at
 * @property-read ?User $user
 * @property-read ?Model $fileable
 * @property-read array $path_info
 */
#[UseFactory(BackupFileFactory::class)]
final class BackupFile extends Model
{
    /** @use HasFactory<BackupFileFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'path',
        'name',
        'disk',
    ];

    /**
     * The attributes that should be visible in serialization.
     *
     * @var list<string>
     */
    protected $visible = [
        'id',
        'name',
        'meta',
        'file_exists',
        'created_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'file_exists',
        'meta',
    ];

    /**
     * Gets the parent user model
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Gets the parent fileable model
     */
    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Deletes file (if removeFileOnDelete in morph model is true) before deleting record from database.
     *
     * @return bool|null
     */
    public function delete()
    {
        if (! is_null($this->fileable)) {
            if (method_exists($this->fileable, 'removeFileOnDelete') && $this->fileable->removeFileOnDelete()) {
                $this->removeFile();
            }

            $this->fileable->delete();
        }

        return parent::delete();
    }

    /**
     * Gets the storage disk the file is from.
     *
     * @return Filesystem
     */
    public function getStorageDisk()
    {
        return Storage::disk($this->disk);
    }

    /**
     * Removes file from storage.
     *
     * @return bool
     */
    public function removeFile()
    {
        return $this->getStorageDisk()->delete($this->path);
    }

    /**
     * Gets whether the file exists or not
     */
    protected function fileExists(): Attribute
    {
        return Attribute::get(function ($value, $attributes = []) {
            try {
                return $this->getStorageDisk()->exists($attributes['path']);
            } catch (\InvalidArgumentException) {
                return false;
            }
        });
    }

    /**
     * Get and set the filename
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => is_null($value) ? Str::of($attributes['path'])->basename() : $value,
            set: fn ($value) => $value,
        );
    }

    /**
     * Get the meta-data for the file
     */
    protected function meta(): Attribute
    {
        $defaults = [];

        return Attribute::get(function ($value, $attributes = []) use ($defaults) {
            $disk = $this->getStorageDisk();

            return $this->file_exists ? [
                'size' => $disk->size($attributes['path']),
                'last_modified' => Carbon::parse($disk->lastModified($attributes['path'])),
                // Don't use the Storage facade to get the mime type, as it will try to download
                // the file, which can cause issues with large files or remote disks. Instead, we
                // will use a custom method to determine the mime type.
                'mime_type' => self::determineMimeType($attributes['path']),
            ] : $defaults;
        });
    }

    /**
     * Gets the pathinfo for the file.
     */
    protected function pathInfo(): Attribute
    {
        return Attribute::get(fn ($value, $attributes = []) => pathinfo(Storage::path($attributes['path'])));
    }

    /**
     * Determines the mime type of a file at a given path.
     *
     * @param  string  $path  The path to the file
     * @return string|null The mime type if detected, or null on failure
     */
    public static function determineMimeType(string $path): ?string
    {
        try {
            $mimeDetector = new FinfoMimeTypeDetector;

            return $mimeDetector->detectMimeTypeFromPath($path);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Creates File model from file path
     *
     * @param  string  $path  Path of file
     * @param  string|null  $name  Filename. If null, filename is generated from path. (default: null)
     * @param  string  $disk  Name of the disk (default: null)
     * @return static
     */
    public static function createFromFilePath(string $path, ?string $name = null, ?string $disk = null): self
    {
        return new self([
            'path' => $path,
            'name' => $name,
            'disk' => $disk,
        ]);
    }
}
