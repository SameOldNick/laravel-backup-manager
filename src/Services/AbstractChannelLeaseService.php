<?php

namespace SameOldNick\BackupManager\Services;

use SameOldNick\BackupManager\Broadcasting\Access\ChannelLease;
use SameOldNick\BackupManager\Concerns;

abstract class AbstractChannelLeaseService
{
    use Concerns\AcquiresChannelLease;
    use Concerns\GeneratesChannelId;

    /**
     * Opens a channel lease for a UUID.
     */
    public function openChannelLeaseForUuid(string $uuid, object $user): ChannelLease
    {
        return $this->openChannelLease($this->createChannelId($uuid), $user);
    }

    /**
     * Retrieves the channel lease for a UUID.
     */
    public function getChannelLeaseForUuid(string $uuid): ?ChannelLease
    {
        return $this->getChannelLease($this->createChannelId($uuid));
    }

    /**
     * Returns the lease for a UUID or throws the configured domain exceptions.
     */
    protected function requireChannelLeaseForUuid(string $uuid, object $user): ChannelLease
    {
        $lease = $this->getChannelLeaseForUuid($uuid);

        if ($lease === null) {
            throw $this->makeChannelLeaseNotFoundException($uuid);
        }

        if (! $this->isChannelLeaseOwnedByUser($lease, $user)) {
            throw $this->makeChannelLeaseUnauthorizedException($uuid);
        }

        return $lease;
    }

    /**
     * Checks whether the lease belongs to the given user.
     */
    protected function isChannelLeaseOwnedByUser(ChannelLease $lease, object $user): bool
    {
        return $lease->notifiableClass === $user::class
            && $lease->notifiableKey === (string) $user->getAuthIdentifier();
    }

    /**
     * Gets the channel lease not found exception for the current workflow.
     */
    abstract protected function makeChannelLeaseNotFoundException(string $uuid): \Throwable;

    /**
     * Gets the channel lease unauthorized exception for the current workflow.
     */
    abstract protected function makeChannelLeaseUnauthorizedException(string $uuid): \Throwable;

    /**
     * Gets the channel ID prefix from configuration.
     */
    abstract protected function getChannelIdPrefix(): string;

    /**
     * Gets the lease expiration in minutes from configuration.
     */
    abstract protected function getChannelLeaseExpirationMinutes(): int;
}