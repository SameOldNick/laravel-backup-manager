<?php

namespace SameOldNick\BackupManager\Models;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use SameOldNick\BackupManager\Contracts\FilesystemConfiguration as FilesystemConfigurationContract;
use SameOldNick\BackupManager\Models\Collections\FilesystemConfigurationCollection;
use SameOldNick\BackupManager\Models\Factories\FilesystemConfigurationFactory;
use Spatie\Backup\Config\Config;

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
#[CollectedBy(FilesystemConfigurationCollection::class)]
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
     * Attaches model event listeners for creating, updating, and saving events to ensure the slug is generated from the name if not provided.
     */
    protected static function booted(): void
    {
        static::creating(function (self $configuration) {
            if (is_null($configuration->slug)) {
                $configuration->slug = Str::slug($configuration->name);
            }
        });

        static::saving(function (self $configuration) {
            if ($configuration->isDirty('name') && ! $configuration->isDirty('slug') && ! is_null($configuration->slug)) {
                $configuration->slug = Str::slug($configuration->name);
            }
        });
    }

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
            'name' => $this->slug,
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
     * Determine if the destination is enabled based on the Spatie Backup config.
     */
    public function isEnabled(Config $backupConfig): bool
    {
        // Pull disks indirectly through Spatie Backup config
        $enabled = $backupConfig->backup->destination->disks;

        return in_array($this->driver_name, $enabled, true);
    }

    /**
     * Determine if the destination is valid based on the existence of the configurable type.
     */
    protected function isValid(): Attribute
    {
        return Attribute::get(fn () => class_exists($this->configurable_type));
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
     * Gets the slug for the filesystem configuration.
     * If the slug is not set, it will be generated from the name.
     */
    protected function slug(): Attribute
    {
        return Attribute::get(fn (?string $value) => $value ?? Str::slug($this->name));
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
     * Scope a query to only include configurations by driver name.
     */
    #[Scope]
    protected function byDriverName(Builder $query, string $driverName): void
    {
        $query->where('slug', Str::after($driverName, 'dynamic-'));
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
            'slug' => $this->slug,
            'type' => $this->disk_type,
            ...$this->configurable->toArray(),
        ];
    }
}
