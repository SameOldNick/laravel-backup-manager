<?php

namespace SameOldNick\BackupManager\Models;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use SameOldNick\BackupManager\Enums\RunStatus;
use SameOldNick\BackupManager\Models\Factories\BackupDestinationTestRunFactory;
use SameOldNick\BackupManager\Models\Factories\BackupRunFactory;

/**
 * @property string $id
 * @property int $filesystem_configuration_id
 * @property FilesystemConfiguration $filesystemConfiguration
 * @property RunStatus $status
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property ?Carbon $started_at
 * @property ?Carbon $completed_at
 */
#[UseFactory(BackupDestinationTestRunFactory::class)]
class BackupDestinationTestRun extends Model
{
    /** @use HasFactory<BackupRunFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'filesystem_configuration_id',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => RunStatus::class,
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * The filesystem configuration that belongs to the test run.
     *
     * @return BelongsTo<FilesystemConfiguration>
     */
    public function filesystemConfiguration(): BelongsTo
    {
        return $this->belongsTo(FilesystemConfiguration::class);
    }
}
