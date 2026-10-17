<?php

namespace SameOldNick\BackupManager\Events;

/**
 * Event triggered when a schedule registration fails due to an exception.
 */
final class ScheduleRegistrationFailed
{
    /**
     * Create a new event instance.
     *
     * @param  string  $scheduleType  The type of schedule (e.g., 'backup', 'cleanup').
     * @param  string|int|null  $scheduleId  The unique identifier of the schedule, or null if not applicable.
     * @param  string  $scheduleName  The name of the schedule.
     * @param  \Throwable  $exception  The exception that caused the registration failure.
     */
    public function __construct(
        public readonly string $scheduleType,
        public readonly string|int|null $scheduleId,
        public readonly string $scheduleName,
        public readonly \Throwable $exception,
    ) {
        //
    }
}
