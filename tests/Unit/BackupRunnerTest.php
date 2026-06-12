<?php

namespace SameOldNick\BackupManager\Tests\Unit;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use SameOldNick\BackupManager\Enums\RunStatus;
use SameOldNick\BackupManager\Models\BackupRun;
use SameOldNick\BackupManager\SpatieBackup\BackupRunner;
use SameOldNick\BackupManager\Tests\TestCase;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Tasks\Backup\BackupJob as SpatieBackupJob;

class BackupRunnerTest extends TestCase
{
    #[Test]
    public function it_calls_on_success_callback_when_backup_succeeds(): void
    {
        $successCalled = false;

        $mockBackupJob = Mockery::mock(SpatieBackupJob::class);
        $mockBackupJob->shouldReceive('run')->once();

        $runner = $this->createPartialMockedRunner(
            mockBackupJob: $mockBackupJob,
            onSuccessCallback: function () use (&$successCalled) {
                $successCalled = true;
            },
        );

        $runner->run(Mockery::mock(Config::class));

        $this->assertTrue($successCalled, 'onSuccessCallback should be called on success.');
    }

    #[Test]
    public function it_calls_on_failed_callback_when_backup_fails(): void
    {
        $failedCalled = false;
        $expectedException = new Exception('Backup failed');

        $mockBackupJob = Mockery::mock(SpatieBackupJob::class);
        $mockBackupJob->shouldReceive('run')->once()->andThrow($expectedException);

        $runner = $this->createPartialMockedRunner(
            mockBackupJob: $mockBackupJob,
            onFailedCallback: function (Exception $e) use (&$failedCalled) {
                $failedCalled = true;
            },
        );

        try {
            $runner->run(Mockery::mock(Config::class));
        } catch (Exception) {
            // Exception is expected to propagate
        }

        $this->assertTrue($failedCalled, 'onFailedCallback should be called on failure.');
    }

    #[Test]
    public function it_passes_the_exception_to_on_failed_callback(): void
    {
        $receivedException = null;
        $expectedException = new Exception('Backup failed');

        $mockBackupJob = Mockery::mock(SpatieBackupJob::class);
        $mockBackupJob->shouldReceive('run')->once()->andThrow($expectedException);

        $runner = $this->createPartialMockedRunner(
            mockBackupJob: $mockBackupJob,
            onFailedCallback: function (Exception $e) use (&$receivedException) {
                $receivedException = $e;
            },
        );

        try {
            $runner->run(Mockery::mock(Config::class));
        } catch (Exception) {
            // Expected to propagate
        }

        $this->assertSame($expectedException, $receivedException, 'onFailedCallback should receive the thrown exception.');
    }

    #[Test]
    public function it_calls_on_completed_callback_after_successful_backup(): void
    {
        $completedCalled = false;

        $mockBackupJob = Mockery::mock(SpatieBackupJob::class);
        $mockBackupJob->shouldReceive('run')->once();

        $runner = $this->createPartialMockedRunner(
            mockBackupJob: $mockBackupJob,
            onCompletedCallback: function () use (&$completedCalled) {
                $completedCalled = true;
            },
        );

        $runner->run(Mockery::mock(Config::class));

        $this->assertTrue($completedCalled, 'onCompletedCallback should be called after a successful backup.');
    }

    #[Test]
    public function it_calls_on_completed_callback_after_failed_backup(): void
    {
        $completedCalled = false;

        $mockBackupJob = Mockery::mock(SpatieBackupJob::class);
        $mockBackupJob->shouldReceive('run')->once()->andThrow(new Exception('Backup failed'));

        $runner = $this->createPartialMockedRunner(
            mockBackupJob: $mockBackupJob,
            onCompletedCallback: function () use (&$completedCalled) {
                $completedCalled = true;
            },
        );

        try {
            $runner->run(Mockery::mock(Config::class));
        } catch (Exception) {
            // Expected to propagate
        }

        $this->assertTrue($completedCalled, 'onCompletedCallback should be called even after a failed backup.');
    }

    #[Test]
    public function it_runs_the_backup_job(): void
    {
        $mockBackupJob = Mockery::mock(SpatieBackupJob::class);
        $mockBackupJob->shouldReceive('run')->once();

        $runner = $this->createPartialMockedRunner(mockBackupJob: $mockBackupJob);

        $runner->run(Mockery::mock(Config::class));

        // Mockery will automatically verify the 'run' expectation via its destructor.
        // If 'run' is not called, the test will fail.
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function it_rethrows_exception_after_calling_failed_and_completed_callbacks(): void
    {
        $callOrder = [];
        $expectedException = new Exception('Backup failed');

        $mockBackupJob = Mockery::mock(SpatieBackupJob::class);
        $mockBackupJob->shouldReceive('run')->once()->andThrow($expectedException);

        $runner = $this->createPartialMockedRunner(
            mockBackupJob: $mockBackupJob,
            onFailedCallback: function () use (&$callOrder) {
                $callOrder[] = 'failed';
            },
            onCompletedCallback: function () use (&$callOrder) {
                $callOrder[] = 'completed';
            },
        );

        $caught = false;
        try {
            $runner->run(Mockery::mock(Config::class));
        } catch (Exception $e) {
            $caught = true;
            $this->assertSame($expectedException, $e);
        }

        $this->assertTrue($caught, 'Exception should propagate after callbacks.');
        $this->assertSame(['failed', 'completed'], $callOrder, 'onFailedCallback should be called before onCompletedCallback.');
    }

    #[Test]
    public function it_does_not_call_success_callback_when_backup_fails(): void
    {
        $successCalled = false;

        $mockBackupJob = Mockery::mock(SpatieBackupJob::class);
        $mockBackupJob->shouldReceive('run')->once()->andThrow(new Exception('Backup failed'));

        $runner = $this->createPartialMockedRunner(
            mockBackupJob: $mockBackupJob,
            onSuccessCallback: function () use (&$successCalled) {
                $successCalled = true;
            },
        );

        try {
            $runner->run(Mockery::mock(Config::class));
        } catch (Exception) {
            // Expected to propagate
        }

        $this->assertFalse($successCalled, 'onSuccessCallback should not be called on failure.');
    }

    #[Test]
    public function it_does_not_call_failed_callback_when_backup_succeeds(): void
    {
        $failedCalled = false;

        $mockBackupJob = Mockery::mock(SpatieBackupJob::class);
        $mockBackupJob->shouldReceive('run')->once();

        $runner = $this->createPartialMockedRunner(
            mockBackupJob: $mockBackupJob,
            onFailedCallback: function () use (&$failedCalled) {
                $failedCalled = true;
            },
        );

        $runner->run(Mockery::mock(Config::class));

        $this->assertFalse($failedCalled, 'onFailedCallback should not be called on success.');
    }

    #[Test]
    public function it_handles_null_callbacks_gracefully(): void
    {
        $mockBackupJob = Mockery::mock(SpatieBackupJob::class);
        $mockBackupJob->shouldReceive('run')->once();

        $runner = $this->createPartialMockedRunner(mockBackupJob: $mockBackupJob);

        // Should not throw any errors when callbacks are null
        $runner->run(Mockery::mock(Config::class));

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function it_handles_null_callbacks_gracefully_on_failure(): void
    {
        $mockBackupJob = Mockery::mock(SpatieBackupJob::class);
        $mockBackupJob->shouldReceive('run')->once()->andThrow(new Exception('Backup failed'));

        $runner = $this->createPartialMockedRunner(mockBackupJob: $mockBackupJob);

        try {
            $runner->run(Mockery::mock(Config::class));
        } catch (Exception) {
            // Expected to propagate
        }

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function create_factory_sets_started_callback_that_updates_backup_run(): void
    {
        $backupRun = BackupRun::factory()->create([
            'status' => RunStatus::Pending,
            'started_at' => null,
        ]);

        $runner = BackupRunner::forBackupRun($backupRun);

        $callback = $this->getCallbackFromRunner($runner, 'onStartedCallback');

        $this->assertIsCallable($callback);

        Model::unguard();
        call_user_func($callback);
        Model::reguard();

        $backupRun->refresh();
        $this->assertEquals(RunStatus::Running, $backupRun->status);
        $this->assertNotNull($backupRun->started_at);
    }

    #[Test]
    public function create_factory_sets_success_callback_that_updates_backup_run(): void
    {
        $backupRun = BackupRun::factory()->create([
            'status' => RunStatus::Running,
        ]);

        $runner = BackupRunner::forBackupRun($backupRun);
        $callback = $this->getCallbackFromRunner($runner, 'onSuccessCallback');

        $this->assertIsCallable($callback);

        call_user_func($callback);

        $backupRun->refresh();
        $this->assertEquals(RunStatus::Successful, $backupRun->status);
    }

    #[Test]
    public function create_factory_sets_failed_callback_that_updates_backup_run(): void
    {
        $backupRun = BackupRun::factory()->create([
            'status' => RunStatus::Running,
        ]);

        $runner = BackupRunner::forBackupRun($backupRun);
        $callback = $this->getCallbackFromRunner($runner, 'onFailedCallback');

        $this->assertIsCallable($callback);

        call_user_func($callback);

        $backupRun->refresh();
        $this->assertEquals(RunStatus::Failed, $backupRun->status);
    }

    #[Test]
    public function create_factory_sets_completed_callback_that_updates_backup_run(): void
    {
        $backupRun = BackupRun::factory()->create([
            'completed_at' => null,
        ]);

        $runner = BackupRunner::forBackupRun($backupRun);
        $callback = $this->getCallbackFromRunner($runner, 'onCompletedCallback');

        $this->assertIsCallable($callback);

        Model::unguard();
        call_user_func($callback);
        Model::reguard();

        $backupRun->refresh();
        $this->assertNotNull($backupRun->completed_at);
    }

    /**
     * Retrieve a callback property from a BackupRunner instance using reflection.
     */
    private function getCallbackFromRunner(BackupRunner $runner, string $property): mixed
    {
        $reflection = new \ReflectionClass($runner);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);

        return $prop->getValue($runner);
    }

    /**
     * Create a partially mocked BackupRunner with a mocked createBackupJob method
     * that returns the given mock SpatieBackupJob.
     *
     * @param  array<string, callable|null>  $callbacks
     */
    private function createPartialMockedRunner(
        SpatieBackupJob $mockBackupJob,
        ?callable $onStartedCallback = null,
        ?callable $onSuccessCallback = null,
        ?callable $onFailedCallback = null,
        ?callable $onCompletedCallback = null,
    ): BackupRunner {
        /** @var MockInterface&BackupRunner $runner */
        $runner = Mockery::mock(BackupRunner::class, [
            $onStartedCallback,
            $onSuccessCallback,
            $onFailedCallback,
            $onCompletedCallback,
        ])->makePartial();

        $runner->shouldReceive('createBackupJob')
            ->andReturn($mockBackupJob);

        return $runner;
    }
}
