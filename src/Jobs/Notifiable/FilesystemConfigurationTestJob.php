<?php

namespace SameOldNick\BackupManager\Jobs\Notifiable;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use SameOldNick\BackupManager\Models\BackupDestinationTestRun;
use SameOldNick\BackupManager\Runners\BackupDestinationTestRunner;

class FilesystemConfigurationTestJob extends NotifiableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly string $uuid,
        string $channel,
        object $notifiable,
    ) {
        parent::__construct($channel, $notifiable);
    }

    /**
     * Handle the job.
     */
    public function handle(): void
    {
        $this->performJob(function () {
            /** @var BackupDestinationTestRun $testRun */
            $testRun = BackupDestinationTestRun::findOrFail($this->uuid);

            $disk = Storage::disk($testRun->filesystemConfiguration->driver_name);
            $testRunner = $this->createTestRunner($testRun);

            // If an exception is thrown, Laravel will handle the rest...
            $testRunner($disk);
        });
    }

    /**
     * Create a test runner for the given test run.
     */
    protected function createTestRunner(BackupDestinationTestRun $testRun): BackupDestinationTestRunner
    {
        return BackupDestinationTestRunner::forTestRun($testRun);
    }
}
