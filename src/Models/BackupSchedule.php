<?php

namespace SameOldNick\BackupManager\Models;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use SameOldNick\BackupManager\Enums\BackupTypes;
use SameOldNick\BackupManager\Models\Collections\BackupScheduleCollection;

/**
 * @property string $id
 * @property string $name
 * @property BackupTypes $type
 * @property string $cron_expression
 * @property bool $is_active
 * @property ?\DateTimeInterface $created_at
 * @property ?\DateTimeInterface $updated_at
 * @property ?\DateTimeInterface $deleted_at
 * @property-read ?\DateTimeInterface $next_run
 */
#[CollectedBy(BackupScheduleCollection::class)]
class BackupSchedule extends AbstractSchedule
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'cron_expression',
        'is_active',
    ];

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'next_run',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The filesystems configurations that belong to the schedule.
     *
     * @return BelongsToMany<FilesystemConfiguration>
     */
    public function filesystemConfigurations(): BelongsToMany
    {
        return $this->belongsToMany(FilesystemConfiguration::class);
    }

    /**
     * Get the backup type as an enum.
     */
    protected function type(): Attribute
    {
        return Attribute::get(fn ($value) => BackupTypes::tryFrom($value));
    }

    /**
     * Get the available backup destinations for scheduling.
     *
     * @return Collection<int, FilesystemConfiguration>
     */
    public static function availableDestinations()
    {
        return FilesystemConfiguration::query()
            ->active()
            ->orderBy('name')
            ->get();
    }
}
