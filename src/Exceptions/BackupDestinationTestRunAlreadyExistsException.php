<?php

namespace SameOldNick\BackupManager\Exceptions;

class BackupDestinationTestRunAlreadyExistsException extends \RuntimeException
{
    /**
     * Initializes BackupDestinationTestRunAlreadyExistsException instance.
     *
     * @param  string  $uuid  The UUID of the backup destination test run that already exists
     */
    public function __construct(
        public readonly string $uuid
    ) {
        parent::__construct("A backup destination test run with UUID '{$uuid}' already exists.");
    }
}
