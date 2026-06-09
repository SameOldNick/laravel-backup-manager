<?php

namespace SameOldNick\BackupManager\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
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
            ->count('backups', 0),
            interacted: false
        );
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
            ->count('backups', 3),
            interacted: false
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
            ->count('data', 5),
            key: 'data.paginated',
            interacted: false
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
            ->count('backups', 1),
            interacted: false
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
            ->count('backups', 1),
            interacted: false
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
            ->count('backups', 0),
            interacted: false
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
            ->count('backups', 1),
            interacted: false
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
            ->count('backups', 1),
            interacted: false
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
            ->count('backups', 1),
            interacted: false
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
            ->count('backups', 1),
            interacted: false
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
}
