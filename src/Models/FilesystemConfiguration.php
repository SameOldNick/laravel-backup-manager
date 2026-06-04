<?php

namespace SameOldNick\BackupManager\Models;

use SameOldNick\BackupManager\Contracts\FilesystemConfiguration as FilesystemConfigurationContract;
use SameOldNick\BackupManager\Models\Factories\FilesystemConfigurationFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $disk_type
 * @property bool $is_active
 * @property string $configurable_type
 * @property int $configurable_id
 * @property string $driver_name
 * @property-read ?Model $configurable
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 *
 * @method static Builder active(bool $isActive = true)
 */
#[UseFactory(FilesystemConfigurationFactory::class)]
class FilesystemConfiguration extends Model implements FilesystemConfigurationContract
{
    /** @use HasFactory<FilesystemConfigurationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'disk_type',
        'is_active',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'configurable',
    ];

    /**
     * Get the owning configurable model.
     */
    public function configurable()
    {
        return $this->morphTo();
    }

    /**
     * {@inheritDoc}
     */
    public function getFilesystemConfig(): array
    {
        $options =
            $this->configurable instanceof FilesystemConfigurationContract ?
            $this->configurable->getFilesystemConfig() :
            [];

        return [
            'name' => Str::slug($this->name),
            'driver' => $this->disk_type,
            ...$options,
        ];
    }

    /**
     * The backup schedules that belong to the filesystem configuration.
     *
     * @return BelongsToMany<BackupSchedule>
     */
    public function backupSchedules(): BelongsToMany
    {
        return $this->belongsToMany(BackupSchedule::class);
    }

    /**
     * Gets the driver name
     * Used to pull the configuration from the filesystem manager
     */
    protected function driverName(): Attribute
    {
        return Attribute::get(fn () => 'dynamic-'.$this->getFilesystemConfig()['name']);
    }

    /**
     * Scope a query to only include active schedules.
     */
    #[Scope]
    protected function active(Builder $query, bool $isActive = true): void
    {
        $query->where('is_active', $isActive);
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return [
            $this->getKeyName() => $this->getKey(),
            'is_active' => $this->is_active,
            'name' => $this->name,
            'type' => $this->disk_type,
            ...$this->configurable->toArray(),
        ];
    }
}
