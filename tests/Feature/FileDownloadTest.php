<?php

namespace SameOldNick\BackupManager\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Mockery;
use SameOldNick\BackupManager\Models\BackupFile;
use SameOldNick\BackupManager\Tests\TestCase;

class FileDownloadTest extends TestCase
{
    public function test_backup_file_downloads_are_streamed_without_using_the_filesystem_download_helper(): void
    {
        $file = BackupFile::factory()->create([
            'disk' => 'backup-downloads',
            'path' => 'backups/archive.zip',
            'name' => 'archive.zip',
        ]);

        $stream = fopen('php://temp', 'rb+');
        fwrite($stream, 'backup-contents');
        rewind($stream);

        $disk = Mockery::mock();
        $disk->shouldReceive('readStream')
            ->once()
            ->with('backups/archive.zip')
            ->andReturn($stream);
        $disk->shouldNotReceive('download');
        $disk->shouldNotReceive('size');
        $disk->shouldNotReceive('mimeType');
        $disk->shouldNotReceive('lastModified');

        Storage::shouldReceive('disk')
            ->once()
            ->with('backup-downloads')
            ->andReturn($disk);

        $response = $this->get(URL::temporarySignedRoute('backup.file', now()->addMinutes(5), ['file' => $file]));

        $response->assertOk();
        $response->assertDownload('archive.zip');
        $this->assertSame('backup-contents', $response->streamedContent());
    }
}
