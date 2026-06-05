<?php

namespace SameOldNick\BackupManager\Tests\Feature;

use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Tests\TestCase;

class BackupScheduleDestinationsTest extends TestCase
{
    public function test_backup_schedule_store_links_selected_destinations(): void
    {
        $admin = $this->createAdmin();

        $destinationOne = FilesystemConfiguration::factory()->local()->create([
            'is_active' => true,
        ]);

        $destinationTwo = FilesystemConfiguration::factory()->ftp()->create([
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('backup.schedules.backup.store'), [
            'name' => 'Daily Files',
            'type' => 'only_files',
            'cron_expression' => '0 0 * * *',
            'is_active' => true,
            'destination_ids' => [$destinationOne->id, $destinationTwo->id],
        ]);

        $response->assertOk();

        $schedule = BackupSchedule::query()->where('name', 'Daily Files')->firstOrFail();

        $this->assertEqualsCanonicalizing(
            [$destinationOne->id, $destinationTwo->id],
            $schedule->filesystemConfigurations()->pluck('filesystem_configurations.id')->all(),
        );
    }

    public function test_backup_schedule_update_syncs_selected_destinations(): void
    {
        $admin = $this->createAdmin();

        $destinationOne = FilesystemConfiguration::factory()->local()->create([
            'is_active' => true,
        ]);

        $destinationTwo = FilesystemConfiguration::factory()->sftp()->create([
            'is_active' => true,
        ]);

        $schedule = BackupSchedule::create([
            'name' => 'Nightly Backup',
            'type' => 'full',
            'cron_expression' => '0 2 * * *',
            'is_active' => true,
        ]);

        $schedule->filesystemConfigurations()->sync([$destinationOne->id]);

        $response = $this->actingAs($admin)->put(route('backup.schedules.backup.update', $schedule), [
            'name' => 'Nightly Full Backup',
            'destination_ids' => [$destinationTwo->id],
        ]);

        $response->assertOk();

        $schedule->refresh();

        $this->assertSame('Nightly Full Backup', $schedule->name);
        $this->assertEquals(
            [$destinationTwo->id],
            $schedule->filesystemConfigurations()->pluck('filesystem_configurations.id')->all(),
        );
    }
}
