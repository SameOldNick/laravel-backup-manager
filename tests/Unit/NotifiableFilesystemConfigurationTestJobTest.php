<?php

namespace SameOldNick\BackupManager\Tests\Unit;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use SameOldNick\BackupManager\Broadcasting\Notifications\JobStatusNotification;
use SameOldNick\BackupManager\Enums\RunStatus;
use SameOldNick\BackupManager\Jobs\Notifiable\FilesystemConfigurationTestJob;
use SameOldNick\BackupManager\Models\BackupDestinationTestRun;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Runners\BackupDestinationTestRunner;
use SameOldNick\BackupManager\Tests\TestCase;
use Workbench\App\Models\User;

class NotifiableFilesystemConfigurationTestJobTest extends TestCase
{
    #[Test]
    public function it_sends_notifications_and_runs_test_on_successful_execution(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $testRun = $this->createTestRunWithDisk();

        $job = $this->createJobWithMockedRunner($user, $testRun, function ($mockRunner) {
            $mockRunner->shouldReceive('__invoke')->once();
        });

        $job->handle();

        // Assert JobStatusNotifier sent start + completed
        Notification::assertSentTo($user, JobStatusNotification::class, function ($notification) {
            return $notification->status === JobStatusNotification::STATUS_STARTED;
        });

        Notification::assertSentTo($user, JobStatusNotification::class, function ($notification) {
            return $notification->status === JobStatusNotification::STATUS_COMPLETED;
        });
    }

    #[Test]
    public function it_sends_failure_notification_when_test_runner_throws(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $testRun = $this->createTestRunWithDisk();

        $expectedException = new Exception('Filesystem test failed');

        $job = $this->createJobWithMockedRunner($user, $testRun, function ($mockRunner) use ($expectedException) {
            $mockRunner->shouldReceive('__invoke')->once()->andThrow($expectedException);
        });

        try {
            $job->handle();
            $this->fail('Expected exception was not thrown.');
        } catch (Exception $e) {
            $this->assertSame($expectedException, $e);
        }

        // Assert JobStatusNotifier sent started but NOT completed
        Notification::assertSentTo($user, JobStatusNotification::class, function ($notification) {
            return $notification->status === JobStatusNotification::STATUS_STARTED;
        });

        Notification::assertNotSentTo($user, JobStatusNotification::class, function ($notification) {
            return $notification->status === JobStatusNotification::STATUS_COMPLETED;
        });
    }

    #[Test]
    public function it_throws_model_not_found_when_test_run_does_not_exist(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        // Real job — createTestRunner is NOT mocked, so findOrFail throws.
        $job = new FilesystemConfigurationTestJob(
            uuid: 'nonexistent-uuid',
            channel: 'backups.test-channel',
            notifiable: $user,
        );

        $this->expectException(ModelNotFoundException::class);

        $job->handle();
    }

    /**
     * Create a partial mock of FilesystemConfigurationTestJob where
     * createTestRunner returns a mocked BackupDestinationTestRunner.
     *
     * @param  \Closure(BackupDestinationTestRunner&MockInterface): void  $configureRunner
     */
    protected function createJobWithMockedRunner(
        User $user,
        BackupDestinationTestRun $testRun,
        \Closure $configureRunner,
    ): FilesystemConfigurationTestJob {
        /** @var BackupDestinationTestRunner&MockInterface $mockRunner */
        $mockRunner = Mockery::mock(BackupDestinationTestRunner::class);

        $configureRunner($mockRunner);

        /** @var FilesystemConfigurationTestJob&MockInterface $partialMock */
        $partialMock = Mockery::mock(FilesystemConfigurationTestJob::class, [
            $testRun->id,
            'backups.test-channel',
            $user,
        ])->makePartial();

        $partialMock->shouldAllowMockingProtectedMethods();
        $partialMock->shouldReceive('createTestRunner')->andReturn($mockRunner);

        return $partialMock;
    }

    /**
     * Set up a FilesystemConfiguration and BackupDestinationTestRun,
     * faking the storage disk so Storage::disk() resolves without error.
     */
    protected function createTestRunWithDisk(): BackupDestinationTestRun
    {
        $fsConfig = FilesystemConfiguration::factory()->local()->create([
            'name' => 'testdisk',
        ]);

        Storage::fake($fsConfig->driver_name);

        /** @var BackupDestinationTestRun $testRun */
        $testRun = BackupDestinationTestRun::factory()->create([
            'filesystem_configuration_id' => $fsConfig->id,
            'status' => RunStatus::Pending,
        ]);

        return $testRun;
    }
}
