<?php

namespace SameOldNick\BackupManager\Tests\Feature;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\Fluent\AssertableJson;
use SameOldNick\BackupManager\Jobs\Notifiable\BackupJob;
use SameOldNick\BackupManager\Models\Backup;
use SameOldNick\BackupManager\Testing\Concerns;
use SameOldNick\BackupManager\Tests\TestCase;

class PerformBackupControllerTest extends TestCase
{
    use Concerns\UiResponderAssertions;

    /**
     * Prepare faked storage for backup-related tests.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    /**
     * It queues a full backup and redirects to the signed perform page.
     */
    public function test_performs_full_backup(): void
    {
        Queue::fake();

        $admin = $this->createAdmin();

        $startResponse = $this->actingAs($admin)->post(route('backup.perform.start'), [
            'type' => 'full',
        ]);

        $startResponse->assertOk();

        $this->assertResponderUsed($startResponse, 'perform');
        $this->assertResponseId($startResponse, 'start');
        $this->assertResponseData($startResponse, fn (AssertableJson $json) => $json
            ->where('type', 'full')
            ->has('uuid')
            ->has('redirectUrl'),
            interacted: false
        );

        $redirectUrl = $startResponse->json('data.redirectUrl');

        $this->assertNotNull($redirectUrl);

        Queue::assertPushedTimes(BackupJob::class, 1);

        Queue::assertPushed(BackupJob::class, function ($job) {
            return $job->backupType === 'full';
        });

        $showPerformResponse = $this->actingAs($admin)->get($redirectUrl);

        $showPerformResponse->assertOk();

        $this->assertResponderUsed($showPerformResponse, 'perform');
        $this->assertResponseId($showPerformResponse, 'perform');
        $this->assertResponseData($showPerformResponse, fn (AssertableJson $json) => $json
            ->where('type', 'full')
            ->has('uuid'),
            interacted: false
        );
    }

    /**
     * It queues a database-only backup and redirects to the signed perform page.
     */
    public function test_performs_database_backup(): void
    {
        Queue::fake();

        $admin = $this->createAdmin();

        $startResponse = $this->actingAs($admin)->post(route('backup.perform.start'), [
            'type' => 'database',
        ]);

        $startResponse->assertOk();

        $this->assertResponderUsed($startResponse, 'perform');
        $this->assertResponseId($startResponse, 'start');
        $this->assertResponseData($startResponse, fn (AssertableJson $json) => $json
            ->where('type', 'database')
            ->has('uuid')
            ->has('redirectUrl'),
            interacted: false
        );

        $redirectUrl = $startResponse->json('data.redirectUrl');

        $this->assertNotNull($redirectUrl);

        Queue::assertPushedTimes(BackupJob::class, 1);

        Queue::assertPushed(BackupJob::class, function ($job) {
            return $job->backupType === 'only_databases';
        });

        $showPerformResponse = $this->actingAs($admin)->get($redirectUrl);

        $showPerformResponse->assertOk();

        $this->assertResponderUsed($showPerformResponse, 'perform');
        $this->assertResponseId($showPerformResponse, 'perform');
        $this->assertResponseData($showPerformResponse, fn (AssertableJson $json) => $json
            ->where('type', 'database')
            ->has('uuid'),
            interacted: false
        );
    }

    /**
     * It queues a files-only backup and redirects to the signed perform page.
     */
    public function test_performs_file_backup(): void
    {
        Queue::fake();

        $admin = $this->createAdmin();

        $startResponse = $this->actingAs($admin)->post(route('backup.perform.start'), [
            'type' => 'files',
        ]);

        $startResponse->assertOk();

        $this->assertResponderUsed($startResponse, 'perform');
        $this->assertResponseId($startResponse, 'start');
        $this->assertResponseData($startResponse, fn (AssertableJson $json) => $json
            ->where('type', 'files')
            ->has('uuid')
            ->has('redirectUrl'),
            interacted: false
        );

        $redirectUrl = $startResponse->json('data.redirectUrl');

        $this->assertNotNull($redirectUrl);

        Queue::assertPushedTimes(BackupJob::class, 1);

        Queue::assertPushed(BackupJob::class, function ($job) {
            return $job->backupType === 'only_files';
        });

        $showPerformResponse = $this->actingAs($admin)->get($redirectUrl);

        $this->assertResponderUsed($showPerformResponse, 'perform');
        $this->assertResponseId($showPerformResponse, 'perform');
        $this->assertResponseData($showPerformResponse, fn (AssertableJson $json) => $json
            ->where('type', 'files')
            ->has('uuid'),
            interacted: false
        );
    }

    /**
     * It shows perform backup data for a valid signed URL.
     */
    public function test_shows_backup_perform(): void
    {
        $admin = $this->createAdmin();

        $uuid = fake()->uuid();

        $url = URL::temporarySignedRoute('backup.perform.show', now()->addMinutes(5), [
            'type' => 'files',
            'uuid' => $uuid,
        ]);

        $response = $this->actingAs($admin)->get($url);

        $response->assertOk();

        $this->assertResponderUsed($response, 'perform');
        $this->assertResponseId($response, 'perform');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->where('type', 'files')
            ->where('uuid', $uuid),
            interacted: false
        );

    }
}
