<?php

namespace SameOldNick\BackupManager\Models;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use SameOldNick\BackupManager\Enums\BackupTypes;
use SameOldNick\BackupManager\Enums\RunStatus;
use SameOldNick\BackupManager\Models\Factories\BackupRunFactory;

/**
 * @property string $id
 * @property BackupTypes $type
 * @property ?array $disks
 * @property RunStatus $status
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property ?Carbon $started_at
 * @property ?Carbon $completed_at
 */
#[UseFactory(BackupRunFactory::class)]
class BackupRun extends Model
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
        'type',
        'disks',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => BackupTypes::class,
        'disks' => 'array',
        'status' => RunStatus::class,
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
