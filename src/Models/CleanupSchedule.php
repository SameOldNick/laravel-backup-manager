<?php

namespace SameOldNick\BackupManager\Models;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Model;
use SameOldNick\BackupManager\Models\Collections\CleanupScheduleCollection;

/**
 * @property string $id
 * @property string $name
 * @property string $cron_expression
 * @property bool $is_active
 * @property ?\DateTimeInterface $created_at
 * @property ?\DateTimeInterface $updated_at
 * @property ?\DateTimeInterface $deleted_at
 * @property-read ?\DateTimeInterface $next_run
 */
#[CollectedBy(CleanupScheduleCollection::class)]
class CleanupSchedule extends AbstractSchedule
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
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
}
