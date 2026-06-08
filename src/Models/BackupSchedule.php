<?php

namespace SameOldNick\BackupManager\Models;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use SameOldNick\BackupManager\Enums\BackupTypes;
use SameOldNick\BackupManager\Models\Collections\BackupScheduleCollection;

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
        'type' => BackupTypes::class,
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
