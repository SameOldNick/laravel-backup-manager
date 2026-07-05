<?php

namespace SameOldNick\BackupManager\Exceptions;

class BackupChannelLeaseNotFoundException extends \RuntimeException
{
    /**
     * Initializes BackupChannelLeaseNotFoundException instance.
     *
     * @param  string  $uuid  The UUID of the backup channel lease that was not found
     */
    public function __construct(
        public readonly string $uuid
    ) {
        parent::__construct("A backup channel lease for UUID '{$uuid}' was not found.");
    }
}
