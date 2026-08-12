<?php

namespace SameOldNick\BackupManager\Exceptions;

class ChannelLeaseUnauthorizedException extends \RuntimeException
{
    /**
     * Initializes ChannelLeaseUnauthorizedException instance.
     *
     * @param  string  $uuid  The UUID of the channel lease that was not authorized for access
     */
    public function __construct(
        public readonly string $uuid
    ) {
        parent::__construct("A channel lease for UUID '{$uuid}' was not authorized for access.");
    }
}
