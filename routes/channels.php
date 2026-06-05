<?php

use Illuminate\Support\Facades\Broadcast;
use SameOldNick\BackupManager\Broadcasting\Channels\BackupsChannel;
use SameOldNick\BackupManager\Broadcasting\Channels\TestDestinationChannel;

Broadcast::channel('backups.{uuid}', BackupsChannel::class);
Broadcast::channel('test-destination.{uuid}', TestDestinationChannel::class);
