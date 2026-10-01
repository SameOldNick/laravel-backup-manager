<?php

namespace SameOldNick\BackupManager\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Translation\PotentiallyTranslatedString;
use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Models\CleanupSchedule;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;

/**
 * Validates a submitted destination id against the destinations a schedule may reference.
 *
 * A destination is selectable when it is active, or when the schedule already has it attached
 * and it has since been deactivated. The edit screens deliberately keep an attached-but
 * deactivated destination visible so an unrelated save does not detach it, which means
 * re-submitting that id has to stay valid. Ids that were never attached are still rejected, so
 * this only preserves an existing link and never grants a new one.
 *
 * Notes on the trust boundary this sits on (browser form post -> pivot sync):
 * - The request declares the shape (`sometimes|array|max:255`, `integer` per element) and
 *   `validated()` drops undeclared keys, so nothing else reaches the service. The array may also be
 *   empty, which is how an app clears a schedule's destinations.
 * - The schedule is passed as `mixed` on purpose and matched with `instanceof`: if the route
 *   parameter is not one of the schedule models the rule falls back to active-only, i.e. it
 *   fails closed rather than failing open.
 * - The id is always bound as a query parameter, never interpolated, and the "already attached"
 *   alternative is grouped inside the existence check so an `or` can never widen it.
 */
class SelectableDestinationId implements ValidationRule
{
    /**
     * @param  mixed  $schedule  The schedule being updated, when the request has one
     */
    public function __construct(
        protected readonly mixed $schedule = null,
    ) {
        //
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $attachedIds = $this->attachedDestinationIds();

        $selectable = FilesystemConfiguration::query()
            ->whereKey($value)
            ->where(function (Builder $query) use ($attachedIds) {
                $query->where('is_active', true);

                if ($attachedIds !== []) {
                    $query->orWhereIn('id', $attachedIds);
                }
            })
            ->exists();

        if (! $selectable) {
            $fail('backup-manager::validation.destination_not_selectable');
        }
    }

    /**
     * Get the ids of the destinations currently attached to the schedule.
     *
     * @return array<int, int>
     */
    protected function attachedDestinationIds(): array
    {
        if (! $this->schedule instanceof BackupSchedule && ! $this->schedule instanceof CleanupSchedule) {
            return [];
        }

        return $this->schedule->filesystemConfigurations()
            ->pluck('filesystem_configurations.id')
            ->all();
    }
}
