<?php

namespace SameOldNick\BackupManager\Models;

use SameOldNick\BackupManager\Enums\BackupTypes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}
