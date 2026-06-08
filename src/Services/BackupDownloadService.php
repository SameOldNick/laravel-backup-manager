<?php

namespace SameOldNick\BackupManager\Services;

use SameOldNick\BackupManager\Models\BackupFile;

class BackupDownloadService
{
    /**
     * Initializes BackupsService instance.
     */
    public function __construct()
    {
        //
    }

    public function openDownloadStream(BackupFile $file)
    {
        $stream = $file->getStorageDisk()->readStream($file->path);

        if (! is_resource($stream)) {
            throw new \RuntimeException('Could not open stream for backup file.');
        }

        return $stream;
    }
}
