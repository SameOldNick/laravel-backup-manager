<?php

namespace SameOldNick\BackupManager\Runners;

use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Tasks\Cleanup\CleanupJob as SpatieCleanupJob;
use Spatie\Backup\Tasks\Cleanup\CleanupStrategy;

class CleanupRunner extends Runner
{
    /**
     * Execute the cleanup process.
     *
     * @param  array<int, string>|null  $disks
     */
    public function __invoke(Config $config, CleanupStrategy $strategy, ?array $disks = null): void
    {
        $this->executeWithCallbacks(function () use ($config, $disks, $strategy) {
            $this->checkForEmptyDisks($disks);

            $destinations = $disks !== null ?
                $this->createBackupDestinations($disks, $config->backup->name) :
                BackupDestinationFactory::createFromArray($config);

            $cleanupJob = $this->createCleanupJob($strategy, $destinations);

            $cleanupJob->run();
        });
    }

    /**
     * Create a CleanupJob based on the given strategy and destinations.
     *
     * @param  Collection<int, BackupDestination>  $destinations
     */
    public function createCleanupJob(CleanupStrategy $strategy, Collection $destinations): SpatieCleanupJob
    {
        $cleanupJob = new SpatieCleanupJob($destinations, $strategy);

        return $cleanupJob;
    }
}
