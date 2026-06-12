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

    /**
     * Opens a read stream for the given backup file.
     *
     * @param  BackupFile  $file  The backup file for which to open the download stream
     * @return resource A read stream resource for the backup file
     *
     * @throws \RuntimeException If the stream could not be opened
     */
    public function openDownloadStream(BackupFile $file)
    {
        $stream = $file->getStorageDisk()->readStream($file->path);

        if (! is_resource($stream)) {
            throw new \RuntimeException('Could not open stream for backup file.');
        }

        return $stream;
    }
}
