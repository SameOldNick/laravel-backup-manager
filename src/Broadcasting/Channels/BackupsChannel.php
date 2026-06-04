<?php

namespace SameOldNick\BackupManager\Broadcasting\Channels;

class BackupsChannel extends AbstractChannel
{
    /**
     * {@inheritDoc}
     */
    public static function getChannelName(): string
    {
        return 'backups';
    }
}
