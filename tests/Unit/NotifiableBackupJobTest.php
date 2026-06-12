<?php

namespace SameOldNick\BackupManager\Tests\Unit;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use SameOldNick\BackupManager\Broadcasting\Notifications\JobStatusNotification;
use SameOldNick\BackupManager\Broadcasting\Notifications\ProcessOutputNotification;
use SameOldNick\BackupManager\Broadcasting\Notifications\ProcessStatusNotification;
use SameOldNick\BackupManager\Broadcasting\Notifiers\ProcessNotifier;
use SameOldNick\BackupManager\Enums\RunStatus;
use SameOldNick\BackupManager\Jobs\Notifiable\BackupJob;
use SameOldNick\BackupManager\Models\BackupRun;
use SameOldNick\BackupManager\Runners\BackupRunner;
use SameOldNick\BackupManager\Tests\TestCase;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Support\BackupLogger;
use Workbench\App\Models\User;

class NotifiableBackupJobTest extends TestCase
{
    #[Test]
    public function it_sends_notifications_and_runs_backup_on_successful_execution(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $backupRun = BackupRun::factory()->create(['status' => RunStatus::Pending]);

        $job = $this->createJobWithMockedRunner($user, $backupRun, function ($mockRunner) {
            $mockRunner->shouldReceive('__invoke')->once();
        });

        $job->handle(app(Config::class), app(BackupLogger::class));

        // Assert JobStatusNotifier sent start + completed
        Notification::assertSentTo($user, JobStatusNotification::class, function ($notification) {
            return $notification->status === JobStatusNotification::STATUS_STARTED;
        });

        Notification::assertSentTo($user, JobStatusNotification::class, function ($notification) {
            return $notification->status === JobStatusNotification::STATUS_COMPLETED;
        });

        // Assert ProcessNotifier sent begin + complete(0)
        Notification::assertSentTo($user, ProcessStatusNotification::class, function ($notification) {
            return $notification->status === ProcessStatusNotification::STATUS_BEGIN;
        });

        Notification::assertSentTo($user, ProcessStatusNotification::class, function ($notification) {
            return $notification->status === ProcessStatusNotification::STATUS_COMPLETE
                && ($notification->extra['error_code'] ?? null) === 0;
        });
    }

    #[Test]
    public function it_sends_failure_notification_when_backup_runner_throws(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $backupRun = BackupRun::factory()->create(['status' => RunStatus::Pending]);

        $expectedException = new Exception('Backup process failed');

        $job = $this->createJobWithMockedRunner($user, $backupRun, function ($mockRunner) use ($expectedException) {
            $mockRunner->shouldReceive('__invoke')->once()->andThrow($expectedException);
        });

        try {
            $job->handle(app(Config::class), app(BackupLogger::class));
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

        // Assert ProcessNotifier sent begin + complete(1)
        Notification::assertSentTo($user, ProcessStatusNotification::class, function ($notification) {
            return $notification->status === ProcessStatusNotification::STATUS_BEGIN;
        });

        Notification::assertSentTo($user, ProcessStatusNotification::class, function ($notification) {
            return $notification->status === ProcessStatusNotification::STATUS_COMPLETE
                && ($notification->extra['error_code'] ?? null) === 1;
        });
    }

    #[Test]
    public function it_throws_model_not_found_when_backup_run_does_not_exist(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        // Real BackupJob — createBackupRunner is NOT mocked, so
        // BackupRun::findOrFail throws when the UUID doesn't exist.
        $job = new BackupJob(
            uuid: 'nonexistent-uuid',
            channel: 'backups.test-channel',
            notifiable: $user,
        );

        $this->expectException(ModelNotFoundException::class);

        $job->handle(app(Config::class), app(BackupLogger::class));
    }

    #[Test]
    public function it_redirects_backup_logger_messages_to_process_notifier(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        // This test never calls handle(), so createBackupRunner is never
        // invoked — no need for a real BackupRun or mocked runner.
        $job = new BackupJob(
            uuid: fake()->uuid(),
            channel: 'backups.test-channel',
            notifiable: $user,
        );

        $notifier = ProcessNotifier::create(
            $user,
            'backups.test-channel',
        );

        $logger = app(BackupLogger::class);
        $logger->clearListeners();

        // Invoke the protected redirect method via reflection
        $reflection = new \ReflectionMethod($job, 'redirectBackupLoggerMessagesToNotifiable');
        $reflection->invoke($job, $logger, $notifier);

        // Trigger logger messages using the public API — the registered
        // listener will route them through the notifier's ProcessOutput.
        $logger->info('Informational message');
        $logger->warn('Warning message');
        $logger->error('Error message');

        // Assert ProcessOutput notifications were sent. The messages go through
        // Symfony's OutputFormatter which wraps them in style tags, so we check
        // that the message contains the original text.
        Notification::assertSentTo($user, ProcessOutputNotification::class, function ($notification) {
            return str_contains($notification->message, 'Informational message');
        });

        Notification::assertSentTo($user, ProcessOutputNotification::class, function ($notification) {
            return str_contains($notification->message, 'Warning message');
        });

        Notification::assertSentTo($user, ProcessOutputNotification::class, function ($notification) {
            return str_contains($notification->message, 'Error message');
        });
    }

    /**
     * Create a partial mock of BackupJob where createBackupRunner returns
     * a mocked BackupRunner, avoiding any real backup execution.
     *
     * @param  \Closure(BackupRunner & MockInterface): void  $configureRunner
     * @param  array<int, mixed>  $extraConstructorArgs  Positional args for BackupJob after uuid, channel, notifiable
     */
    protected function createJobWithMockedRunner(
        User $user,
        BackupRun $backupRun,
        \Closure $configureRunner,
        array $extraConstructorArgs = [],
    ): BackupJob {
        /** @var BackupRunner&MockInterface $mockRunner */
        $mockRunner = Mockery::mock(BackupRunner::class);

        $configureRunner($mockRunner);

        // Constructor signature: uuid, channel, notifiable, backupType, disks
        $constructorArgs = [
            $backupRun->id,
            'backups.test-channel',
            $user,
            ...$extraConstructorArgs,
        ];

        /** @var BackupJob&MockInterface $partialMock */
        $partialMock = Mockery::mock(BackupJob::class, $constructorArgs)->makePartial();

        $partialMock->shouldAllowMockingProtectedMethods();
        $partialMock->shouldReceive('createBackupRunner')->andReturn($mockRunner);

        return $partialMock;
    }
}
