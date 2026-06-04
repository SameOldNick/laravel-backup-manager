<?php

namespace SameOldNick\BackupManager\Models;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use SameOldNick\BackupManager\Models\Collections\BackupCollection;
use SameOldNick\BackupManager\Models\Factories\BackupFactory;
use Spatie\Backup\BackupDestination\Backup as SpatieBackup;
use Spatie\Backup\BackupDestination\BackupDestination;

/**
 * @property string $uuid
 * @property ?string $error_message
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property ?Carbon $deleted_at
 * @property-read string $status One of STATUS_* constants
 */
#[CollectedBy(BackupCollection::class)]
#[UseFactory(BackupFactory::class)]
class Backup extends Model
{
    /** @use HasFactory<BackupFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    const STATUS_SUCCESSFUL = 'successful';

    const STATUS_FAILED = 'failed';

    const STATUS_FILE_NOT_FOUND = 'file_not_found';

    const STATUS_DELETED = 'deleted';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'error_message',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var list<string>
     */
    protected $with = [
        'file',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'status',
    ];

    /**
     * Gets the BackupFile for this Model.
     *
     * @return MorphOne
     */
    public function file()
    {
        return $this->morphOne(BackupFile::class, 'fileable');
    }

    /**
     * Checks if file should be removed when model is deleted.
     *
     * @return bool
     */
    public function removeFileOnDelete()
    {
        return true;
    }

    /**
     * Checks if deleted.
     */
    public function isDeleted(): bool
    {
        return $this->trashed();
    }

    /**
     * Checks if failed.
     */
    public function isFailed(): bool
    {
        return ! empty($this->error_message);
    }

    /**
     * Checks if doesn't exist.
     */
    public function isNotExists(): bool
    {
        return is_null($this->file) || ! $this->file->file_exists;
    }

    /**
     * Status accessor
     */
    protected function status(): Attribute
    {
        return new Attribute(
            get: fn () => match (true) {
                $this->isDeleted() => static::STATUS_DELETED,
                $this->isFailed() => static::STATUS_FAILED,
                $this->isNotExists() => static::STATUS_FILE_NOT_FOUND,
                default => static::STATUS_SUCCESSFUL
            }
        );
    }

    /**
     * Creates BackupFile from backup.
     */
    public static function createFile(SpatieBackup $backup, BackupDestination $backupDestination, $user = null): BackupFile
    {
        $file = BackupFile::createFromFilePath($backup->path(), disk: $backupDestination->diskName());

        if (! is_null($user)) {
            $file->user()->associate($user);
        }

        return $file;
    }
}
