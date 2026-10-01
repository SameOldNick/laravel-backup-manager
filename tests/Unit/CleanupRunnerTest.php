<?php

namespace SameOldNick\BackupManager\Tests\Unit;

use Exception;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use SameOldNick\BackupManager\Runners\CleanupRunner;
use SameOldNick\BackupManager\Tests\TestCase;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Tasks\Cleanup\CleanupJob as SpatieCleanupJob;
use Spatie\Backup\Tasks\Cleanup\CleanupStrategy;

class CleanupRunnerTest extends TestCase
{
    #[Test]
    public function it_calls_on_success_callback_when_cleanup_succeeds(): void
    {
        $successCalled = false;

        $mockCleanupJob = Mockery::mock(SpatieCleanupJob::class);
        $mockCleanupJob->shouldReceive('run')->once();

        $runner = $this->createPartialMockedRunner(
            mockCleanupJob: $mockCleanupJob,
            onSuccessCallback: function () use (&$successCalled) {
                $successCalled = true;
            },
        );

        $runner(app(Config::class), Mockery::mock(CleanupStrategy::class));

        $this->assertTrue($successCalled, 'onSuccessCallback should be called on success.');
    }

    #[Test]
    public function it_calls_on_failed_callback_when_cleanup_fails(): void
    {
        $failedCalled = false;
        $expectedException = new Exception('Cleanup failed');

        $mockCleanupJob = Mockery::mock(SpatieCleanupJob::class);
        $mockCleanupJob->shouldReceive('run')->once()->andThrow($expectedException);

        $runner = $this->createPartialMockedRunner(
            mockCleanupJob: $mockCleanupJob,
            onFailedCallback: function (Exception $e) use (&$failedCalled) {
                $failedCalled = true;
            },
        );

        try {
            $runner(app(Config::class), Mockery::mock(CleanupStrategy::class));
        } catch (Exception) {
            // Exception is expected to propagate
        }

        $this->assertTrue($failedCalled, 'onFailedCallback should be called on failure.');
    }

    #[Test]
    public function it_passes_the_exception_to_on_failed_callback(): void
    {
        $receivedException = null;
        $expectedException = new Exception('Cleanup failed');

        $mockCleanupJob = Mockery::mock(SpatieCleanupJob::class);
        $mockCleanupJob->shouldReceive('run')->once()->andThrow($expectedException);

        $runner = $this->createPartialMockedRunner(
            mockCleanupJob: $mockCleanupJob,
            onFailedCallback: function (Exception $e) use (&$receivedException) {
                $receivedException = $e;
            },
        );

        try {
            $runner(app(Config::class), Mockery::mock(CleanupStrategy::class));
        } catch (Exception) {
            // Expected to propagate
        }

        $this->assertSame($expectedException, $receivedException, 'onFailedCallback should receive the thrown exception.');
    }

    #[Test]
    public function it_calls_on_completed_callback_after_successful_cleanup(): void
    {
        $completedCalled = false;

        $mockCleanupJob = Mockery::mock(SpatieCleanupJob::class);
        $mockCleanupJob->shouldReceive('run')->once();

        $runner = $this->createPartialMockedRunner(
            mockCleanupJob: $mockCleanupJob,
            onCompletedCallback: function () use (&$completedCalled) {
                $completedCalled = true;
            },
        );

        $runner(app(Config::class), Mockery::mock(CleanupStrategy::class));

        $this->assertTrue($completedCalled, 'onCompletedCallback should be called after a successful cleanup.');
    }

    #[Test]
    public function it_calls_on_completed_callback_after_failed_cleanup(): void
    {
        $completedCalled = false;

        $mockCleanupJob = Mockery::mock(SpatieCleanupJob::class);
        $mockCleanupJob->shouldReceive('run')->once()->andThrow(new Exception('Cleanup failed'));

        $runner = $this->createPartialMockedRunner(
            mockCleanupJob: $mockCleanupJob,
            onCompletedCallback: function () use (&$completedCalled) {
                $completedCalled = true;
            },
        );

        try {
            $runner(app(Config::class), Mockery::mock(CleanupStrategy::class));
        } catch (Exception) {
            // Expected to propagate
        }

        $this->assertTrue($completedCalled, 'onCompletedCallback should be called even after a failed cleanup.');
    }

    #[Test]
    public function it_runs_the_cleanup_job(): void
    {
        $mockCleanupJob = Mockery::mock(SpatieCleanupJob::class);
        $mockCleanupJob->shouldReceive('run')->once();

        $runner = $this->createPartialMockedRunner(mockCleanupJob: $mockCleanupJob);

        $runner(app(Config::class), Mockery::mock(CleanupStrategy::class));

        // Mockery will automatically verify the 'run' expectation via its destructor.
        // If 'run' is not called, the test will fail.
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function it_rethrows_exception_after_calling_failed_and_completed_callbacks(): void
    {
        $callOrder = [];
        $expectedException = new Exception('Cleanup failed');

        $mockCleanupJob = Mockery::mock(SpatieCleanupJob::class);
        $mockCleanupJob->shouldReceive('run')->once()->andThrow($expectedException);

        $runner = $this->createPartialMockedRunner(
            mockCleanupJob: $mockCleanupJob,
            onFailedCallback: function () use (&$callOrder) {
                $callOrder[] = 'failed';
            },
            onCompletedCallback: function () use (&$callOrder) {
                $callOrder[] = 'completed';
            },
        );

        $caught = false;
        try {
            $runner(app(Config::class), Mockery::mock(CleanupStrategy::class));
        } catch (Exception $e) {
            $caught = true;
            $this->assertSame($expectedException, $e);
        }

        $this->assertTrue($caught, 'Exception should propagate after callbacks.');
        $this->assertSame(['failed', 'completed'], $callOrder, 'onFailedCallback should be called before onCompletedCallback.');
    }

    #[Test]
    public function it_does_not_call_success_callback_when_cleanup_fails(): void
    {
        $successCalled = false;

        $mockCleanupJob = Mockery::mock(SpatieCleanupJob::class);
        $mockCleanupJob->shouldReceive('run')->once()->andThrow(new Exception('Cleanup failed'));

        $runner = $this->createPartialMockedRunner(
            mockCleanupJob: $mockCleanupJob,
            onSuccessCallback: function () use (&$successCalled) {
                $successCalled = true;
            },
        );

        try {
            $runner(app(Config::class), Mockery::mock(CleanupStrategy::class));
        } catch (Exception) {
            // Expected to propagate
        }

        $this->assertFalse($successCalled, 'onSuccessCallback should not be called on failure.');
    }

    #[Test]
    public function it_does_not_call_failed_callback_when_cleanup_succeeds(): void
    {
        $failedCalled = false;

        $mockCleanupJob = Mockery::mock(SpatieCleanupJob::class);
        $mockCleanupJob->shouldReceive('run')->once();

        $runner = $this->createPartialMockedRunner(
            mockCleanupJob: $mockCleanupJob,
            onFailedCallback: function () use (&$failedCalled) {
                $failedCalled = true;
            },
        );

        $runner(app(Config::class), Mockery::mock(CleanupStrategy::class));

        $this->assertFalse($failedCalled, 'onFailedCallback should not be called on success.');
    }

    #[Test]
    public function it_handles_null_callbacks_gracefully(): void
    {
        $mockCleanupJob = Mockery::mock(SpatieCleanupJob::class);
        $mockCleanupJob->shouldReceive('run')->once();

        $runner = $this->createPartialMockedRunner(mockCleanupJob: $mockCleanupJob);

        // Should not throw any errors when callbacks are null
        $runner(app(Config::class), Mockery::mock(CleanupStrategy::class));

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function it_handles_null_callbacks_gracefully_on_failure(): void
    {
        $mockCleanupJob = Mockery::mock(SpatieCleanupJob::class);
        $mockCleanupJob->shouldReceive('run')->once()->andThrow(new Exception('Cleanup failed'));

        $runner = $this->createPartialMockedRunner(mockCleanupJob: $mockCleanupJob);

        try {
            $runner(app(Config::class), Mockery::mock(CleanupStrategy::class));
        } catch (Exception) {
            // Expected to propagate
        }

        $this->addToAssertionCount(1);
    }

    /**
     * Create a partially mocked CleanupRunner with a mocked createCleanupJob method
     * that returns the given mock SpatieCleanupJob.
     *
     * @param  SpatieCleanupJob  $mockCleanupJob  The mock cleanup job to return from createCleanupJob
     * @param  ?callable  $onStartedCallback  Optional callback to execute when the cleanup starts
     * @param  ?callable  $onSuccessCallback  Optional callback to execute when the cleanup succeeds
     * @param  ?callable  $onFailedCallback  Optional callback to execute when the cleanup fails
     * @param  ?callable  $onCompletedCallback  Optional callback to execute when the cleanup completes (regardless of success or failure)
     */
    private function createPartialMockedRunner(
        SpatieCleanupJob $mockCleanupJob,
        ?callable $onStartedCallback = null,
        ?callable $onSuccessCallback = null,
        ?callable $onFailedCallback = null,
        ?callable $onCompletedCallback = null,
    ): CleanupRunner {
        /** @var MockInterface&CleanupRunner $runner */
        $runner = Mockery::mock(CleanupRunner::class, [
            $onStartedCallback,
            $onSuccessCallback,
            $onFailedCallback,
            $onCompletedCallback,
        ])->makePartial();

        $runner->shouldReceive('createCleanupJob')
            ->andReturn($mockCleanupJob);

        return $runner;
    }
}
