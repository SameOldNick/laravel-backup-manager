<?php

namespace SameOldNick\BackupManager\Broadcasting\Notifiers;

use SameOldNick\BackupManager\Broadcasting\Notifications\BroadcastNotification;
use Illuminate\Support\Facades\Notification;

abstract class AbstractNotifier
{
    /**
     * Initializes instance
     *
     * @param  string  $channel  Channel
     * @param  object  $notifiable  Who to route notifications to
     */
    public function __construct(
        public readonly string $channel,
        public readonly object $notifiable,
    ) {
        //
    }

    /**
     * Sends notification to notifiable
     *
     * @param  mixed  $notifiable
     * @return void
     */
    public function notify($notifiable, BroadcastNotification $notification)
    {
        Notification::send($notifiable, $notification);
    }
}
