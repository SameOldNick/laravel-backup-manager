<?php

namespace SameOldNick\BackupManager\Exceptions;

class NoValidDisksException extends \InvalidArgumentException
{
    public static function forRun(): self
    {
        return new self('No valid disks found for run with specified disks.');
    }
}
