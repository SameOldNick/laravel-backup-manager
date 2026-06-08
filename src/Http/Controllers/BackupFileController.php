<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use Illuminate\Support\Facades\Log;
use SameOldNick\BackupManager\Models\BackupFile;
use SameOldNick\BackupManager\Services\BackupDownloadService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupFileController
{
    private const STREAM_CHUNK_SIZE = 1024 * 1024;

    public function __construct(
        protected readonly BackupDownloadService $service,
    ) {
        //
    }

    /**
     * Responds with file contents
     */
    public function retrieve(BackupFile $file): StreamedResponse
    {
        try {
            $stream = $this->service->openDownloadStream($file);
        } catch (\RuntimeException $e) {
            Log::error('Error opening backup file stream.', [
                'file_id' => $file->id,
                'disk' => $file->disk,
                'path' => $file->path,
                'error_message' => $e->getMessage(),
            ]);

            abort(404, __('backup::messages.file_not_found'));
        }

        return response()->streamDownload(function () use ($stream): void {
            set_time_limit(0);

            try {
                // Stream the file contents in chunks to avoid loading the entire file into memory.
                while (! feof($stream)) {
                    $chunk = fread($stream, self::STREAM_CHUNK_SIZE);

                    if ($chunk === false) {
                        break;
                    }

                    if ($chunk === '') {
                        continue;
                    }

                    echo $chunk;

                    if (function_exists('ob_flush') && ob_get_level() > 0) {
                        ob_flush();
                    }

                    flush();
                }
            } finally {
                fclose($stream);
            }
        }, $file->name);
    }
}
