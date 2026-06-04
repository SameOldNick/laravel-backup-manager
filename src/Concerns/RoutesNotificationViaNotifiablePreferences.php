<?php

namespace SameOldNick\BackupManager\Concerns;

use App\Components\Notifications\Concerns\RoutesViaNotifiablePreferences;
use App\NotificationChannels;
use SameOldNick\BackupManager\Enums\BackupNotificationTypes;

trait RoutesNotificationViaNotifiablePreferences
{
    use RoutesViaNotifiablePreferences;

    /**
     * Get the specific notification type for this notification.
     *
     * This is used to look up the user's preferences for this notification.
     */
    abstract public function notificationType(): BackupNotificationTypes;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable = null): array
    {
        if ($notifiable === null) {
            return [];
        }

        return $this->viaNotifiablePreferencesWithDefaults(
            $notifiable,
            $this->notificationType(),
            [
                NotificationChannels::Ntfy,
                NotificationChannels::Mail,
            ],
            $this->getDefaultChannels($notifiable, $this->notificationType())
        );
    }
}
