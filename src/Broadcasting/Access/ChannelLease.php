<?php

namespace SameOldNick\BackupManager\Broadcasting\Access;

use Carbon\CarbonInterface;

class ChannelLease
{
    /**
     * Initializes ChannelLease instance.
     *
     * @param  ChannelAccessManager  $manager  Manager to handle channel access operations
     * @param  string  $channelId  Channel ID (e.g. "jobs.{uuid}")
     * @param  string  $notifiableClass  The class name of the notifiable entity
     * @param  string  $notifiableKey  The key to identify the notifiable entity (e.g. database ID)
     * @param  CarbonInterface|null  $expiresAt  When the channel lease expires, or null for no expiration
     */
    public function __construct(
        private readonly ChannelAccessManager $manager,
        public readonly string $channelId,
        public readonly string $notifiableClass,
        public readonly string $notifiableKey,
        public readonly ?CarbonInterface $expiresAt
    ) {
        //
    }

    /**
     * Retrieves the notifiable instance associated with this channel lease.
     *
     * @return object|null The notifiable instance or null if it cannot be found
     */
    public function getNotifiable(): ?object
    {
        if (! class_exists($this->notifiableClass)) {
            return null;
        }

        $notifiableClass = $this->notifiableClass;
        $notifiable = $notifiableClass::find($this->notifiableKey);

        return $notifiable;
    }

    /**
     * Checks if the channel lease has expired.
     *
     * @return bool True if the lease has expired, false otherwise
     */
    public function isExpired(): bool
    {
        return $this->expiresAt !== null && now()->greaterThan($this->expiresAt);
    }

    /**
     * Closes the channel lease, removing it from the access manager.
     */
    public function close(): void
    {
        $this->manager->close($this->channelId);
    }
}
