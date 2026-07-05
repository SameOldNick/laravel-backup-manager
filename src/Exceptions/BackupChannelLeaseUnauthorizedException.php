<?php

namespace SameOldNick\BackupManager\Exceptions;

class BackupChannelLeaseUnauthorizedException extends \RuntimeException
{
    /**
     * Initializes BackupChannelLeaseUnauthorizedException instance.
     *
     * @param  string  $uuid  The UUID of the backup channel lease that was not authorized for access
     */
    public function __construct(
        public readonly string $uuid
    ) {
        parent::__construct("A backup channel lease for UUID '{$uuid}' was not authorized for access.");
    }
}
