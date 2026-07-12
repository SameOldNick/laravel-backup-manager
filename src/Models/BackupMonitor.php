<?php

namespace SameOldNick\BackupManager\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes;

class BackupMonitor extends Model
{
    protected $fillable = [
        'name',
        'disks',
        'maximum_age_in_days',
        'maximum_storage_in_megabytes',
        'is_active',
    ];

    protected $casts = [
        'disks' => 'array',
        'maximum_age_in_days' => 'integer',
        'maximum_storage_in_megabytes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function isEnabled(): bool
    {
        return $this->is_active;
    }

    public function getHealthChecks(): array
    {
        return array_filter([
            MaximumAgeInDays::class => $this->maximum_age_in_days,
            MaximumStorageInMegabytes::class => $this->maximum_storage_in_megabytes,
        ]);
    }
}
