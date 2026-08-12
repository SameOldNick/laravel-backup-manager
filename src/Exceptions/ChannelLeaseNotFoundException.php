<?php

namespace SameOldNick\BackupManager\Exceptions;

class ChannelLeaseNotFoundException extends \RuntimeException
{
    /**
     * Initializes ChannelLeaseNotFoundException instance.
     *
     * @param  string  $uuid  The UUID of the channel lease that was not found
     */
    public function __construct(
        public readonly string $uuid
    ) {
        parent::__construct("A channel lease for UUID '{$uuid}' was not found.");
    }
}
