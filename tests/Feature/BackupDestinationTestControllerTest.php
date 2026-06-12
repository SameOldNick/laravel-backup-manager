<?php

namespace SameOldNick\BackupManager\Tests\Feature;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\Fluent\AssertableJson;
use SameOldNick\BackupManager\Jobs\Notifiable\FilesystemConfigurationTestJob;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Testing\Concerns;
use SameOldNick\BackupManager\Tests\TestCase;

class BackupDestinationTestControllerTest extends TestCase
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
     * Tests that a local backup destination configuration can be successfully tested.
     */
    public function test_local_destination_tests_successfully(): void
    {
        $fsConfig = FilesystemConfiguration::factory()->local()->create([
            'is_active' => true,
        ]);

        $this->performBackupDestinationTest($fsConfig);
    }

    /**
     * Tests that an FTP backup destination configuration can be successfully tested.
     */
    public function test_ftp_destination_tests_successfully(): void
    {
        $fsConfig = FilesystemConfiguration::factory()->ftp()->create([
            'is_active' => true,
        ]);

        $this->performBackupDestinationTest($fsConfig);
    }

    /**
     * Tests that an SFTP backup destination configuration can be successfully tested.
     */
    public function test_sftp_destination_tests_successfully(): void
    {
        $fsConfig = FilesystemConfiguration::factory()->sftp('key')->create([
            'is_active' => true,
        ]);

        $this->performBackupDestinationTest($fsConfig);
    }

    /**
     * It shows perform view when test is in progress.
     */
    public function test_shows_backup_destination_test(): void
    {
        $admin = $this->createAdmin();

        $fsConfig = FilesystemConfiguration::factory()->sftp('key')->create([
            'is_active' => true,
        ]);

        $uuid = fake()->uuid();

        $url = URL::temporarySignedRoute('backup.destinations.test.show', now()->addMinutes(5), [
            'destination' => $fsConfig->id,
            'uuid' => $uuid,
        ]);

        $response = $this->actingAs($admin)->get($url);

        $response->assertOk();

        $this->assertResponderUsed($response, 'backup-destination-test');
        $this->assertResponseId($response, 'perform');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->where('uuid', $uuid),
            interacted: false
        );

    }

    /**
     * It fails when starting the same channel twice
     */
    public function test_fails_when_starting_same_channel_twice(): void
    {
        Queue::fake();

        $fsConfig = FilesystemConfiguration::factory()->local()->create([
            'is_active' => true,
        ]);

        $admin = $this->createAdmin();

        $initializeResponse = $this->actingAs($admin)->post(route('backup.destinations.test.initialize', [
            'destination' => $fsConfig->id,
        ]));

        $initializeResponse->assertOk();

        $startUrl = $initializeResponse->json('data.startUrl');
        $uuid = $initializeResponse->json('data.uuid');

        $firstStartResponse = $this->actingAs($admin)->post($startUrl, [
            'uuid' => $uuid,
        ]);

        $firstStartResponse->assertOk();

        $secondStartResponse = $this->actingAs($admin)->post($startUrl, [
            'uuid' => $uuid,
        ]);

        $secondStartResponse->assertServerError();

        Queue::assertPushedTimes(FilesystemConfigurationTestJob::class, 1);

    }

    /**
     * Helper method to perform a backup destination test for a given filesystem configuration.
     *
     * @param  FilesystemConfiguration  $fsConfig  The filesystem configuration to test
     */
    private function performBackupDestinationTest(FilesystemConfiguration $fsConfig): void
    {
        Queue::fake();

        $admin = $this->createAdmin();

        $initializeResponse = $this->actingAs($admin)->post(route('backup.destinations.test.initialize', [
            'destination' => $fsConfig->id,
        ]));

        $initializeResponse->assertOk();

        $this->assertResponderUsed($initializeResponse, 'backup-destination-test');
        $this->assertResponseId($initializeResponse, 'initialize');
        $this->assertResponseData($initializeResponse, fn (AssertableJson $json) => $json
            ->has('uuid')
            ->where('configuration', fn (Collection $configuration) => $configuration['id'] === $fsConfig->id &&
                $configuration['name'] === $fsConfig->name
            )
            ->has('lease')
            ->has('startUrl')
            ->has('showUrl'),
            interacted: false
        );

        $uuid = $initializeResponse->json('data.uuid');
        $startUrl = $initializeResponse->json('data.startUrl');
        $showUrl = $initializeResponse->json('data.showUrl');

        $startResponse = $this->actingAs($admin)->post($startUrl, [
            'uuid' => $uuid,
        ]);

        $this->assertResponderUsed($startResponse, 'backup-destination-test');
        $this->assertResponseId($startResponse, 'start');
        $this->assertResponseData($startResponse, fn (AssertableJson $json) => $json
            ->where('configuration', fn (Collection $configuration) => $configuration['id'] === $fsConfig->id &&
                $configuration['name'] === $fsConfig->name
            )
            ->has('uuid')
            ->has('lease'),
            interacted: false
        );

        Queue::assertPushedTimes(FilesystemConfigurationTestJob::class, 1);

        Queue::assertPushed(FilesystemConfigurationTestJob::class, function (FilesystemConfigurationTestJob $job) use ($uuid) {
            return $job->uuid === $uuid;
        });

        $showPerformResponse = $this->actingAs($admin)->get($showUrl);

        $showPerformResponse->assertOk();

        $this->assertResponderUsed($showPerformResponse, 'backup-destination-test');
        $this->assertResponseId($showPerformResponse, 'perform');
        $this->assertResponseData($showPerformResponse, fn (AssertableJson $json) => $json
            ->where('configuration', fn (Collection $configuration) => $configuration['id'] === $fsConfig->id &&
                $configuration['name'] === $fsConfig->name
            )
            ->has('backupConfig')
            ->has('lease')
            ->has('uuid'),
            interacted: false
        );
    }
}
