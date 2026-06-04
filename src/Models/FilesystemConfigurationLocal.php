<?php

namespace SameOldNick\BackupManager\Models;

use SameOldNick\BackupManager\Contracts\FilesystemConfiguration as FilesystemConfigurationContract;
use SameOldNick\BackupManager\Models\Factories\FilesystemConfigurationLocalFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $root
 * @property array|null $extra
 * @property-read ?FilesystemConfiguration $filesystemConfiguration
 */
#[UseFactory(FilesystemConfigurationLocalFactory::class)]
class FilesystemConfigurationLocal extends Model implements FilesystemConfigurationContract
{
    /** @use HasFactory<FilesystemConfigurationLocalFactory> */
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    protected $table = 'filesystem_configuration_local';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'root',
        'extra',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'extra' => 'array',
        ];
    }

    /**
     * Gets base FilesystemConfiguration model.
     */
    public function filesystemConfiguration()
    {
        return $this->morphOne(FilesystemConfiguration::class, 'configurable');
    }

    /**
     * {@inheritDoc}
     */
    public function getFilesystemConfig(): array
    {
        $extra = $this->extra ?? [];

        return [
            'root' => storage_path($this->root),
            ...$extra,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return [
            'root' => $this->root,
            'extra' => $this->extra,
        ];
    }
}
