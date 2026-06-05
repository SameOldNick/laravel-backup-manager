<?php

namespace SameOldNick\BackupManager\Tests\Feature;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\Fluent\AssertableJson;
use SameOldNick\BackupManager\Jobs\Notifiable\BackupJob;
use SameOldNick\BackupManager\Models\Backup;
use SameOldNick\BackupManager\Models\Factories\BackupFileFactory;
use SameOldNick\BackupManager\Testing\Concerns;
use SameOldNick\BackupManager\Tests\TestCase;

class BackupControllerTest extends TestCase
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
     * It shows an empty backups list when no backups exist.
     */
    public function test_backups_list_empty(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('backup.backups.index'));

        $response->assertOk();

        $this->assertResponderUsed($response, 'backups');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backups.data', 0)
            ->where('backups.total', 0));
    }

    /**
     * It shows backups when records exist.
     */
    public function test_backups_list_populated(): void
    {
        $admin = $this->createAdmin();

        Backup::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('backup.backups.index'));

        $response->assertOk();

        $this->assertResponderUsed($response, 'backups');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backups.data', 3)
            ->where('backups.total', 3)
        );

    }

    /**
     * It honors the per-page query parameter.
     */
    public function test_backups_shows_per_page(): void
    {
        $admin = $this->createAdmin();

        Backup::factory()->count(15)->create();

        $response = $this->actingAs($admin)->get(route('backup.backups.index', [
            'per_page' => 5,
        ]));

        $response->assertOk();

        $this->assertResponderUsed($response, 'backups');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backups.data', 5)
            ->where('backups.total', 15)
        );

    }

    /**
     * It filters backups by UUID through the query parameter.
     */
    public function test_backups_filtered_by_uuid(): void
    {
        $admin = $this->createAdmin();

        Backup::factory()->create(['uuid' => 'some-uuid']);
        Backup::factory()->count(15)->create();

        $response = $this->actingAs($admin)->get(route('backup.backups.index', [
            'query' => 'some-uuid',
        ]));

        $response->assertOk();
        $this->assertResponderUsed($response, 'backups');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backups.data', 1)
            ->where('backups.total', 16)
        );

    }

    /**
     * It filters backups by status text through the query parameter.
     */
    public function test_backups_filtered_by_status(): void
    {
        $admin = $this->createAdmin();

        Backup::factory()->create(['error_message' => 'Some error occurred']);
        Backup::factory()->count(15)->create();

        $response = $this->actingAs($admin)->get(route('backup.backups.index', [
            'query' => 'failed',
        ]));

        $response->assertOk();
        $this->assertResponderUsed($response, 'backups');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backups.data', 1)
            ->where('backups.total', 16)
        );

    }

    /**
     * It filters backups by file path text through the query parameter.
     */
    public function test_backups_filtered_by_path(): void
    {
        $admin = $this->createAdmin();

        Backup::factory()->successful(
            BackupFileFactory::new()->fromContents('test.txt', 'Test content', disk: 'local')->create()
        )->create();
        Backup::factory()->count(15)->create();

        $response = $this->actingAs($admin)->get(route('backup.backups.index', [
            'query' => 'test',
        ]));

        $response->assertOk();
        $this->assertResponderUsed($response, 'backups');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backups.data', 1)
            ->where('backups.total', 16)
        );

    }

    /**
     * It returns all statuses when status is set to all.
     */
    public function test_backups_shows_all_statuses(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('backup.backups.index', [
            'status' => 'all',
        ]));

        $response->assertOk();
        $this->assertResponderUsed($response, 'backups');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backups.data', 0)
            ->where('backups.total', 0)
        );

    }

    /**
     * It filters backups to successful status.
     */
    public function test_backups_shows_successful(): void
    {
        $admin = $this->createAdmin();

        Backup::factory()->successful()->create();

        $response = $this->actingAs($admin)->get(route('backup.backups.index', [
            'status' => 'successful',
        ]));

        $response->assertOk();
        $this->assertResponderUsed($response, 'backups');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backups.data', 1)
            ->where('backups.total', 1)
        );

    }

    /**
     * It filters backups to failed status.
     */
    public function test_backups_shows_failed(): void
    {
        $admin = $this->createAdmin();

        Backup::factory()->failed()->create();

        $response = $this->actingAs($admin)->get(route('backup.backups.index', [
            'status' => 'failed',
        ]));

        $response->assertOk();
        $this->assertResponderUsed($response, 'backups');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backups.data', 1)
            ->where('backups.total', 1)
        );

    }

    /**
     * It filters backups to file_not_found status.
     */
    public function test_backups_shows_not_found(): void
    {
        $admin = $this->createAdmin();

        Backup::factory()->fileNotFound()->create();

        $response = $this->actingAs($admin)->get(route('backup.backups.index', [
            'status' => 'file_not_found',
        ]));

        $response->assertOk();
        $this->assertResponderUsed($response, 'backups');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backups.data', 1)
            ->where('backups.total', 1)
        );

    }

    /**
     * It filters backups to deleted status.
     */
    public function test_backups_shows_deleted(): void
    {
        $admin = $this->createAdmin();

        Backup::factory()->deleted()->create();

        $response = $this->actingAs($admin)->get(route('backup.backups.index', [
            'status' => 'deleted',
        ]));

        $response->assertOk();
        $this->assertResponderUsed($response, 'backups');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backups.data', 1)
            ->where('backups.total', 1)
        );

    }

    /**
     * It generates a temporary download link and streams file contents.
     */
    public function test_generates_backup_download_link(): void
    {
        Storage::fake('local');

        $admin = $this->createAdmin();

        $backup = Backup::factory()->successful(
            BackupFileFactory::new()->fromContents('test.txt', 'Test content', disk: 'local')->create()
        )->create();

        $redirectResponse = $this->actingAs($admin)->get(route('backup.backups.download', [
            'backup' => $backup->getKey(),
        ]));

        $response = $this->followRedirects($redirectResponse);

        $response->assertOk();
        $response->assertStreamedContent('Test content');
    }

    /**
     * It queues a full backup and redirects to the signed perform page.
     */
    public function test_performs_full_backup(): void
    {
        Queue::fake();

        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('backup.backups.perform'), [
            'type' => 'full',
        ]);

        $response->assertFound();
        $response->assertRedirect();

        $location = $response->headers->get('Location');

        $this->assertNotNull($location);
        $this->assertMatchesRegularExpression('#/backup/backups/perform/full/[0-9a-fA-F-]{36}\?#', $location);

        Queue::assertPushedTimes(BackupJob::class, 1);

        Queue::assertPushed(BackupJob::class, function ($job) {
            return $job->backupType === 'full' && $job->disks === null;
        });

        $showPerformResponse = $this->actingAs($admin)->get($location);

        $showPerformResponse->assertOk();

        $this->assertResponderUsed($showPerformResponse, 'backups');
        $this->assertResponseId($showPerformResponse, 'perform');
        $this->assertResponseData($showPerformResponse, fn (AssertableJson $json) => $json
            ->where('type', 'full')
            ->has('uuid')
        );
    }

    /**
     * It queues a database-only backup and redirects to the signed perform page.
     */
    public function test_performs_database_backup(): void
    {
        Queue::fake();

        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('backup.backups.perform'), [
            'type' => 'database',
        ]);

        $response->assertFound();
        $response->assertRedirect();

        $location = $response->headers->get('Location');

        $this->assertNotNull($location);
        $this->assertMatchesRegularExpression('#/backup/backups/perform/database/[0-9a-fA-F-]{36}\?#', $location);

        Queue::assertPushedTimes(BackupJob::class, 1);

        Queue::assertPushed(BackupJob::class, function ($job) {
            return $job->backupType === 'only_databases' && $job->disks === null;
        });

        $showPerformResponse = $this->actingAs($admin)->get($location);

        $showPerformResponse->assertOk();

        $this->assertResponderUsed($showPerformResponse, 'backups');
        $this->assertResponseId($showPerformResponse, 'perform');
        $this->assertResponseData($showPerformResponse, fn (AssertableJson $json) => $json
            ->where('type', 'database')
            ->has('uuid')
        );
    }

    /**
     * It queues a files-only backup and redirects to the signed perform page.
     */
    public function test_performs_file_backup(): void
    {
        Queue::fake();

        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('backup.backups.perform'), [
            'type' => 'files',
        ]);

        $response->assertFound();
        $response->assertRedirect();

        $location = $response->headers->get('Location');

        $this->assertNotNull($location);
        $this->assertMatchesRegularExpression('#/backup/backups/perform/files/[0-9a-fA-F-]{36}\?#', $location);

        Queue::assertPushedTimes(BackupJob::class, 1);

        Queue::assertPushed(BackupJob::class, function ($job) {
            return $job->backupType === 'only_files' && $job->disks === null;
        });

        $showPerformResponse = $this->actingAs($admin)->get($location);

        $this->assertResponderUsed($showPerformResponse, 'backups');
        $this->assertResponseId($showPerformResponse, 'perform');
        $this->assertResponseData($showPerformResponse, fn (AssertableJson $json) => $json
            ->where('type', 'files')
            ->has('uuid')
        );
    }

    /**
     * It shows perform backup data for a valid signed URL.
     */
    public function test_shows_backup_perform(): void
    {
        $admin = $this->createAdmin();

        $uuid = fake()->uuid();

        $url = URL::temporarySignedRoute('backup.backups.perform.show', now()->addMinutes(5), [
            'type' => 'files',
            'uuid' => $uuid,
        ]);

        $response = $this->actingAs($admin)->get($url);

        $response->assertOk();

        $this->assertResponderUsed($response, 'backups');
        $this->assertResponseId($response, 'perform');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->where('type', 'files')
            ->where('uuid', $uuid)
        );

    }
}
