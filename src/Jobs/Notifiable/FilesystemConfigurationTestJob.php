<?php

namespace SameOldNick\BackupManager\Jobs\Notifiable;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

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
        string $channel,
        object $notifiable,
        protected string $diskName,
    ) {
        parent::__construct($channel, $notifiable);
    }

    /**
     * Handle the job.
     */
    public function handle(): void
    {
        $this->performJob(function () {
            $disk = Storage::disk($this->diskName);

            // Test putting a file
            $testFileName = 'backup_test_'.time().'.txt';
            $content = 'This is a test file for backup destination configuration.';
            if (! $disk->put($testFileName, $content)) {
                throw new Exception("Failed to write test file \"$testFileName\" during filesystem configuration test.");
            }

            // Test getting the file content
            $retrievedContent = $disk->get($testFileName);
            if ($retrievedContent !== $content) {
                throw new Exception("File content mismatch for \"$testFileName\" during filesystem configuration test.");
            }

            // Test deleting the file
            if (! $disk->delete($testFileName)) {
                throw new Exception("Failed to delete test file \"$testFileName\" during filesystem configuration test.");
            }

            // If an exception is thrown, Laravel will handle the rest...
        });
    }
}
