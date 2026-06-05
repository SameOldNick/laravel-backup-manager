<?php

namespace SameOldNick\BackupManager\Tests\Unit;

use Illuminate\Notifications\Events\BroadcastNotificationCreated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use SameOldNick\BackupManager\Broadcasting\Notifications\JobStatusNotification;
use SameOldNick\BackupManager\Tests\TestCase;

class BroadcastNotificationTest extends TestCase
{
    /**
     * Backup progress notifications should broadcast immediately from the running job.
     */
    public function test_job_status_notifications_use_the_sync_connection_for_broadcasts(): void
    {
        Event::fake([BroadcastNotificationCreated::class]);

        Notification::send(new class {}, new JobStatusNotification(
            channel: 'backups.test-channel',
            status: JobStatusNotification::STATUS_STARTED,
        ));

        Event::assertDispatched(BroadcastNotificationCreated::class, function (BroadcastNotificationCreated $event): bool {
            return $event->connection === 'sync'
                && $event->queue === null
                && $event->broadcastAs() === 'job-status'
                && $event->data['status'] === JobStatusNotification::STATUS_STARTED;
        });
    }
}
