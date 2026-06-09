<?php

namespace SameOldNick\BackupManager\Tests\Feature;

use SameOldNick\BackupManager\Enums\BackupTypes;
use SameOldNick\BackupManager\Jobs\BackupJob;
use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Testing\Concerns;
use SameOldNick\BackupManager\Tests\TestCase;

class BackupScheduleControllerTest extends TestCase
{
    use Concerns\SchedulerTestHelpers;
    use Concerns\UiResponderAssertions;

    public function test_creates_full_backup_schedule(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('backup.schedules.backup.store'), [
            'name' => 'Daily Full Backup',
            'type' => 'full',
            'cron_expression' => '0 0 * * *',
            'is_active' => true,
        ]);

        $response->assertOk();

        $this->assertResponderUsed($response, 'backup-schedules');
        $this->assertResponseId($response, 'store');

        $schedule = BackupSchedule::query()->where('name', 'Daily Full Backup')->firstOrFail();

        $this->assertSame(BackupTypes::Full, $schedule->type);
        $this->assertTrue($schedule->is_active);

        $this->assertSchedulerJobs(function (array $jobs) {
            $this->assertCount(1, $jobs);
            $this->assertSame('0 0 * * *', $jobs[0]['expression']);
            $this->assertInstanceOf(BackupJob::class, $jobs[0]['job']);
            $this->assertSame(BackupJob::BACKUP_FULL, $jobs[0]['job']->backupType);
        });
    }

    public function test_creates_databases_backup_schedule(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('backup.schedules.backup.store'), [
            'name' => 'Daily Database Backup',
            'type' => 'databases',
            'cron_expression' => '0 1 * * *',
            'is_active' => true,
        ]);

        $response->assertOk();

        $schedule = BackupSchedule::query()->where('name', 'Daily Database Backup')->firstOrFail();

        $this->assertSame(BackupTypes::Databases, $schedule->type);
    }

    public function test_creates_files_backup_schedule(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('backup.schedules.backup.store'), [
            'name' => 'Daily Files Backup',
            'type' => 'files',
            'cron_expression' => '0 2 * * *',
            'is_active' => true,
        ]);

        $response->assertOk();

        $schedule = BackupSchedule::query()->where('name', 'Daily Files Backup')->firstOrFail();

        $this->assertSame(BackupTypes::Files, $schedule->type);
    }

    public function test_creates_backup_schedule_with_all_destinations(): void
    {
        $admin = $this->createAdmin();

        $destinationOne = FilesystemConfiguration::factory()->local()->create(['is_active' => true]);
        $destinationTwo = FilesystemConfiguration::factory()->ftp()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post(route('backup.schedules.backup.store'), [
            'name' => 'Archive With All Destinations',
            'type' => 'full',
            'cron_expression' => '0 3 * * *',
            'is_active' => true,
            'destination_ids' => [$destinationOne->id, $destinationTwo->id],
        ]);

        $response->assertOk();

        $schedule = BackupSchedule::query()->where('name', 'Archive With All Destinations')->firstOrFail();

        $this->assertEqualsCanonicalizing(
            [$destinationOne->id, $destinationTwo->id],
            $schedule->filesystemConfigurations()->pluck('filesystem_configurations.id')->all(),
        );
    }

    public function test_creates_backup_schedule_for_single_active_destination(): void
    {
        $admin = $this->createAdmin();

        $destination = FilesystemConfiguration::factory()->local()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post(route('backup.schedules.backup.store'), [
            'name' => 'Archive With One Destination',
            'type' => 'files',
            'cron_expression' => '0 4 * * *',
            'is_active' => true,
            'destination_ids' => [$destination->id],
        ]);

        $response->assertOk();

        $schedule = BackupSchedule::query()->where('name', 'Archive With One Destination')->firstOrFail();

        $this->assertEquals([$destination->id], $schedule->filesystemConfigurations()->pluck('filesystem_configurations.id')->all());
    }

    public function test_rejects_single_inactive_destination(): void
    {
        $admin = $this->createAdmin();

        $destination = FilesystemConfiguration::factory()->local()->create(['is_active' => false]);

        $response = $this->actingAs($admin)
            ->from(route('backup.schedules.backup.create'))
            ->post(route('backup.schedules.backup.store'), [
                'name' => 'Inactive Destination Schedule',
                'type' => 'files',
                'cron_expression' => '0 5 * * *',
                'is_active' => true,
                'destination_ids' => [$destination->id],
            ]);

        $response->assertRedirect(route('backup.schedules.backup.create'));
        $response->assertSessionHasErrors(['destination_ids.0']);
    }

    public function test_updates_backup_schedule_type_from_full_to_files(): void
    {
        $admin = $this->createAdmin();

        $schedule = BackupSchedule::create([
            'name' => 'Type Switch Schedule',
            'type' => 'full',
            'cron_expression' => '0 6 * * *',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('backup.schedules.backup.update', $schedule), [
            'type' => 'files',
        ]);

        $response->assertOk();

        $schedule->refresh();

        $this->assertSame(BackupTypes::Files, $schedule->type);
    }

    public function test_sets_backup_schedule_as_inactive(): void
    {
        $admin = $this->createAdmin();

        $schedule = BackupSchedule::create([
            'name' => 'Deactivate Schedule',
            'type' => 'full',
            'cron_expression' => '0 7 * * *',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('backup.schedules.backup.update', $schedule), [
            'is_active' => false,
        ]);

        $response->assertOk();

        $schedule->refresh();

        $this->assertFalse($schedule->is_active);
    }

    public function test_changes_backup_schedule_destinations(): void
    {
        $admin = $this->createAdmin();

        $destinationOne = FilesystemConfiguration::factory()->local()->create(['is_active' => true]);
        $destinationTwo = FilesystemConfiguration::factory()->ftp()->create(['is_active' => true]);

        $schedule = BackupSchedule::create([
            'name' => 'Destination Switch Schedule',
            'type' => 'full',
            'cron_expression' => '0 8 * * *',
            'is_active' => true,
        ]);

        $schedule->filesystemConfigurations()->sync([$destinationOne->id]);

        $response = $this->actingAs($admin)->put(route('backup.schedules.backup.update', $schedule), [
            'destination_ids' => [$destinationTwo->id],
        ]);

        $response->assertOk();

        $schedule->refresh();

        $this->assertEquals([$destinationTwo->id], $schedule->filesystemConfigurations()->pluck('filesystem_configurations.id')->all());
    }

    public function test_changes_backup_schedule_cron_expression(): void
    {
        $admin = $this->createAdmin();

        $schedule = BackupSchedule::create([
            'name' => 'Cron Switch Schedule',
            'type' => 'full',
            'cron_expression' => '0 9 * * *',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('backup.schedules.backup.update', $schedule), [
            'cron_expression' => '30 1 * * *',
        ]);

        $response->assertOk();

        $schedule->refresh();

        $this->assertSame('30 1 * * *', $schedule->cron_expression);
    }

    public function test_removes_backup_schedule(): void
    {
        $admin = $this->createAdmin();

        $schedule = BackupSchedule::create([
            'name' => 'Remove Schedule',
            'type' => 'full',
            'cron_expression' => '0 10 * * *',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('backup.schedules.backup.destroy', $schedule));

        $response->assertOk();

        $this->assertResponderUsed($response, 'backup-schedules');
        $this->assertResponseId($response, 'destroy');

        $this->assertFalse(BackupSchedule::query()->whereKey($schedule->id)->exists());
    }
}
