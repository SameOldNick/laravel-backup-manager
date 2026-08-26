<?php

namespace SameOldNick\BackupManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SameOldNick\BackupManager\Runners\CleanupRunner;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Tasks\Cleanup\CleanupStrategy;

class CleanupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly ?array $disks = null,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(Config $config, CleanupStrategy $strategy, CleanupRunner $cleanupRunner): void
    {
        $cleanupRunner($config, $strategy, $this->disks);
    }
}
