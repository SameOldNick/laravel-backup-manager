<?php

namespace SameOldNick\BackupManager\Broadcasting\Channels;

class TestDestinationChannel extends AbstractChannel
{
    /**
     * {@inheritDoc}
     */
    public static function getChannelName(): string
    {
        return 'test-destination';
    }
}
