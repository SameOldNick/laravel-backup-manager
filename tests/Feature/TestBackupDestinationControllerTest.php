<?php

namespace SameOldNick\BackupManager\Tests\Feature;

use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\Fluent\AssertableJson;
use SameOldNick\BackupManager\Jobs\Notifiable\FilesystemConfigurationTestJob;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Testing\Concerns;
use SameOldNick\BackupManager\Tests\TestCase;
use Spatie\Backup\Config\Config;

class TestBackupDestinationControllerTest extends TestCase
{
    use Concerns\UiResponderAssertions;

    public function test_local_destination_can_be_tested_successfully(): void
    {
        $this->assertDestinationTestResult(
            FilesystemConfiguration::factory()->local()->create(['is_active' => true]),
            true
        );
    }

    public function test_local_destination_can_be_tested_and_report_failure(): void
    {
        $this->assertDestinationTestResult(
            FilesystemConfiguration::factory()->local()->create(['is_active' => false]),
            false
        );
    }

    public function test_ftp_destination_can_be_tested_successfully(): void
    {
        $this->assertDestinationTestResult(
            FilesystemConfiguration::factory()->ftp()->create(['is_active' => true]),
            true
        );
    }

    public function test_ftp_destination_can_be_tested_and_report_failure(): void
    {
        $this->assertDestinationTestResult(
            FilesystemConfiguration::factory()->ftp()->create(['is_active' => false]),
            false
        );
    }

    public function test_sftp_destination_can_be_tested_successfully(): void
    {
        $this->assertDestinationTestResult(
            FilesystemConfiguration::factory()->sftp('key')->create(['is_active' => true]),
            true
        );
    }

    public function test_sftp_destination_can_be_tested_and_report_failure(): void
    {
        $this->assertDestinationTestResult(
            FilesystemConfiguration::factory()->sftp('key')->create(['is_active' => false]),
            false
        );
    }

    private function assertDestinationTestResult(FilesystemConfiguration $destination, bool $enabled): void
    {
        Queue::fake();
        Config::rebind();

        $admin = $this->createAdmin();

        $startResponse = $this->actingAs($admin)->post(route('backup.destinations.test', $destination));

        $startResponse->assertRedirect();

        Queue::assertPushed(FilesystemConfigurationTestJob::class);

        $resultResponse = $this->actingAs($admin)->followRedirects($startResponse);

        $resultResponse->assertOk();

        $this->assertResponderUsed($resultResponse, 'backup-destinations');
        $this->assertResponseId($resultResponse, 'test-result');
        $this->assertResponseData($resultResponse, fn (AssertableJson $json) => $json
            ->where('enabled', $enabled)
            ->where('configuration.id', $destination->id)
            ->has('uuid'),
            interacted: false
        );
    }
}
