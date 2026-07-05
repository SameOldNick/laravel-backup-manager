<?php

namespace SameOldNick\BackupManager\Exceptions;

class BackupRunAlreadyExistsException extends \RuntimeException
{
    /**
     * Initializes BackupRunAlreadyExistsException instance.
     *
     * @param  string  $uuid  The UUID of the backup run that already exists
     */
    public function __construct(
        public readonly string $uuid
    ) {
        parent::__construct("A backup run with UUID '{$uuid}' already exists.");
    }
}
