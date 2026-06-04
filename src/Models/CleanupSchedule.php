<?php

namespace SameOldNick\BackupManager\Models;

use Illuminate\Database\Eloquent\Model;

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
