<?php

namespace SameOldNick\BackupManager\Tests\Feature;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\Fluent\AssertableJson;
use SameOldNick\BackupManager\Jobs\Notifiable\BackupJob;
use SameOldNick\BackupManager\Models\Backup;
use SameOldNick\BackupManager\Models\BackupRun;
use SameOldNick\BackupManager\Services\PerformBackupService;
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
        $this->performBackup('full', BackupJob::BACKUP_FULL);
    }

    /**
     * It queues a database-only backup and redirects to the signed perform page.
     */
    public function test_performs_database_backup(): void
    {
        $this->performBackup('databases', BackupJob::BACKUP_ONLY_DATABASES);
    }

    /**
     * It queues a files-only backup and redirects to the signed perform page.
     */
    public function test_performs_file_backup(): void
    {
        $this->performBackup('files', BackupJob::BACKUP_ONLY_FILES);
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

    /**
     * It fails when starting the same backup channel twice.
     */
    public function test_fails_when_starting_same_channel_twice(): void
    {
        Queue::fake();

        $admin = $this->createAdmin();

        $initializeResponse = $this->actingAs($admin)->post(route('backup.perform.initialize'), [
            'type' => 'full',
        ]);

        $initializeResponse->assertOk();

        $startUrl = $initializeResponse->json('data.startUrl');
        $uuid = $initializeResponse->json('data.uuid');

        $firstStartResponse = $this->actingAs($admin)->post($startUrl, [
            'type' => 'full',
            'uuid' => $uuid,
        ]);

        $firstStartResponse->assertOk();

        $secondStartResponse = $this->actingAs($admin)->post($startUrl, [
            'type' => 'full',
            'uuid' => $uuid,
        ]);

        $secondStartResponse->assertConflict();

        Queue::assertPushedTimes(BackupJob::class, 1);

        $this->assertDatabaseHas((new BackupRun)->getTable(), [
            'id' => $uuid,
        ]);
    }

    public function test_fails_when_starting_backup_with_invalid_lease(): void
    {
        Queue::fake();

        $admin = $this->createAdmin();

        $initializeResponse = $this->actingAs($admin)->post(route('backup.perform.initialize'), [
            'type' => 'full',
        ]);

        $initializeResponse->assertOk();

        $startUrl = $initializeResponse->json('data.startUrl');
        $uuid = $initializeResponse->json('data.uuid');

        // Simulate an invalid lease by deleting the backup run record
        app(PerformBackupService::class)->getBackupChannelLease($uuid)?->close();

        $startResponse = $this->actingAs($admin)->post($startUrl, [
            'type' => 'full',
            'uuid' => $uuid,
        ]);

        $startResponse->assertNotFound();

        Queue::assertNothingPushed();
    }

    public function test_fails_when_starting_backup_with_invalid_lease_user(): void
    {
        Queue::fake();

        $admin = $this->createAdmin();
        $other = $this->createAdmin();

        $initializeResponse = $this->actingAs($admin)->post(route('backup.perform.initialize'), [
            'type' => 'full',
        ]);

        $initializeResponse->assertOk();

        $startUrl = $initializeResponse->json('data.startUrl');
        $uuid = $initializeResponse->json('data.uuid');

        // Simulate an invalid lease by attempting to start the backup with a different user
        $startResponse = $this->actingAs($other)->post($startUrl, [
            'type' => 'full',
            'uuid' => $uuid,
        ]);

        $startResponse->assertForbidden();

        Queue::assertNothingPushed();
    }

    /**
     * Helper method to perform a backup and assert the expected interactions.
     *
     * @param  string  $type  The type of backup to perform (e.g., 'full', 'databases', 'files').
     * @param  string  $expectedBackupType  The expected backup type constant for the queued job.
     */
    private function performBackup(string $type, string $expectedBackupType): void
    {
        Queue::fake();

        $admin = $this->createAdmin();

        $initializeResponse = $this->actingAs($admin)->post(route('backup.perform.initialize'), [
            'type' => $type,
        ]);

        $initializeResponse->assertOk();

        $this->assertResponderUsed($initializeResponse, 'perform');
        $this->assertResponseId($initializeResponse, 'initialize');
        $this->assertResponseData($initializeResponse, fn (AssertableJson $json) => $json
            ->where('type', $type)
            ->has('uuid')
            ->has('startUrl')
            ->has('showUrl'),
            interacted: false
        );

        $startUrl = $initializeResponse->json('data.startUrl');
        $showUrl = $initializeResponse->json('data.showUrl');

        $startResponse = $this->actingAs($admin)->post($startUrl, [
            'type' => $type,
            'uuid' => $initializeResponse->json('data.uuid'),
        ]);

        $this->assertResponderUsed($startResponse, 'perform');
        $this->assertResponseId($startResponse, 'start');
        $this->assertResponseData($startResponse, fn (AssertableJson $json) => $json
            ->where('type', $type)
            ->has('uuid')
            ->has('lease'),
            interacted: false
        );

        Queue::assertPushedTimes(BackupJob::class, 1);

        Queue::assertPushed(BackupJob::class, function (BackupJob $job) use ($expectedBackupType) {
            return $job->backupType === $expectedBackupType;
        });

        $showPerformResponse = $this->actingAs($admin)->get($showUrl);

        $showPerformResponse->assertOk();

        $this->assertResponderUsed($showPerformResponse, 'perform');
        $this->assertResponseId($showPerformResponse, 'perform');
        $this->assertResponseData($showPerformResponse, fn (AssertableJson $json) => $json
            ->where('type', $type)
            ->has('uuid'),
            interacted: false
        );
    }
}
