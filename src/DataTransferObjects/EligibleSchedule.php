<?php

namespace SameOldNick\BackupManager\DataTransferObjects;

final readonly class EligibleSchedule
{
    /**
     * @param  string  $expression  The cron expression representing the schedule's timing.
     * @param  ?array<int, string>  $disks  An array of eligible disk names for the schedule, or null if no disks are specified.
     */
    public function __construct(
        public string $expression,
        public ?array $disks,
    ) {
        //
    }
}
