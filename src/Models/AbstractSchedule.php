<?php

namespace SameOldNick\BackupManager\Models;

use Cron\CronExpression;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $name
 * @property string $cron_expression
 * @property bool $is_active
 *
 * @method static Builder active(bool $isActive = true)
 */
abstract class AbstractSchedule extends Model
{
    /**
     * Get the next run time based on the cron expression.
     */
    protected function nextRun(): Attribute
    {
        return Attribute::get(function ($value, $attributes) {
            try {
                $cron = new CronExpression($attributes['cron_expression']);

                return Carbon::instance($cron->getNextRunDate())->toISO8601String();
            } catch (\Exception $e) {
                return null;
            }
        });
    }

    /**
     * Scope a query to only include active schedules.
     */
    #[Scope]
    protected function active(Builder $query, bool $isActive = true): void
    {
        $query->where('is_active', $isActive);
    }
}
