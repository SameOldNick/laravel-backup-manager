<?php

namespace SameOldNick\BackupManager\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use SameOldNick\BackupManager\Models\BackupFile;
use SameOldNick\BackupManager\Testing\Concerns;
use SameOldNick\BackupManager\Tests\TestCase;

class BackupFileControllerTest extends TestCase
{
    use Concerns\UiResponderAssertions;

    public function test_downloads_backup_file(): void
    {
        Storage::fake('backup-downloads');

        Storage::disk('backup-downloads')->put('backups/archive.zip', 'backup-contents');

        $file = BackupFile::factory()->create([
            'disk' => 'backup-downloads',
            'path' => 'backups/archive.zip',
            'name' => 'archive.zip',
        ]);

        $response = $this->get(URL::temporarySignedRoute('backup.file', now()->addMinutes(5), [
            'file' => $file,
        ]));

        $response->assertOk();
        $response->assertDownload('archive.zip');
        $response->assertStreamedContent('backup-contents');
    }
}
