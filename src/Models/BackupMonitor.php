<?php

namespace SameOldNick\BackupManager\Models;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use SameOldNick\BackupManager\Models\Factories\BackupMonitorFactory;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes;

#[UseFactory(BackupMonitorFactory::class)]
class BackupMonitor extends Model
{
    /** @use HasFactory<BackupMonitorFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'maximum_age_in_days',
        'maximum_storage_in_megabytes',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'maximum_age_in_days' => 'integer',
        'maximum_storage_in_megabytes' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Determine if the backup monitor is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->is_active;
    }

    /**
     * Get the filesystem configurations associated with the backup monitor.
     */
    public function filesystemConfigurations(): BelongsToMany
    {
        return $this->belongsToMany(
            FilesystemConfiguration::class,
            'backup_monitor_filesystem_configuration',
            'backup_monitor_id',
            'filesystem_configuration_id'
        )
            ->withTimestamps();
    }

    /**
     * Get the health checks for the backup monitor.
     */
    public function getHealthChecks(): array
    {
        return array_filter([
            MaximumAgeInDays::class => $this->maximum_age_in_days,
            MaximumStorageInMegabytes::class => $this->maximum_storage_in_megabytes,
        ]);
    }
}
