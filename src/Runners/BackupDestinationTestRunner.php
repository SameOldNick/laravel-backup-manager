<?php

namespace SameOldNick\BackupManager\Runners;

use Illuminate\Contracts\Filesystem\Filesystem;
use SameOldNick\BackupManager\Enums\RunStatus;
use SameOldNick\BackupManager\Models\BackupDestinationTestRun;

class BackupDestinationTestRunner extends Runner
{
    /**
     * Executes a backup destination test run by performing filesystem operations on the given disk.
     *
     * @param  Filesystem  $disk  The filesystem disk to test
     *
     * @throws \Exception If any of the filesystem operations fail
     */
    public function __invoke(Filesystem $disk): void
    {
        $this->executeWithCallbacks(function () use ($disk) {
            $this->performFilesystemTest($disk);
        });
    }

    /**
     * Performs a series of filesystem operations to test the backup destination configuration.
     *
     * @param  Filesystem  $disk  The filesystem disk to test
     *
     * @throws \Exception If any of the filesystem operations fail
     */
    protected function performFilesystemTest(Filesystem $disk): void
    {
        // Test putting a file
        $testFileName = 'backup_test_'.time().'.txt';
        $content = 'This is a test file for backup destination configuration.';
        if (! $disk->put($testFileName, $content)) {
            throw new \Exception("Failed to write test file \"$testFileName\" during filesystem configuration test.");
        }

        // Test getting the file content
        $retrievedContent = $disk->get($testFileName);
        if ($retrievedContent !== $content) {
            throw new \Exception("File content mismatch for \"$testFileName\" during filesystem configuration test.");
        }

        // Test deleting the file
        if (! $disk->delete($testFileName)) {
            throw new \Exception("Failed to delete test file \"$testFileName\" during filesystem configuration test.");
        }
    }

    /**
     * Create a BackupDestinationTestRunner with callbacks wired to update the given BackupRun model.
     */
    public static function forTestRun(BackupDestinationTestRun $testRun): self
    {
        return new self(
            onStartedCallback: static fn () => $testRun->update(['status' => RunStatus::Running, 'started_at' => now()]),
            onSuccessCallback: static fn () => $testRun->update(['status' => RunStatus::Successful]),
            onFailedCallback: static fn () => $testRun->update(['status' => RunStatus::Failed]),
            onCompletedCallback: static fn () => $testRun->update(['completed_at' => now()]),
        );
    }
}
