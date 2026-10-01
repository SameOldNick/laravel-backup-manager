<?php

namespace SameOldNick\BackupManager\Tests\Feature;

use SameOldNick\BackupManager\Models\CleanupSchedule;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Testing\Concerns;
use SameOldNick\BackupManager\Tests\TestCase;

class CleanupScheduleControllerTest extends TestCase
{
    use Concerns\UiResponderAssertions;

    public function test_creates_active_cleanup_schedule(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('backup.schedules.cleanup.store'), [
            'name' => 'Daily Cleanup',
            'cron_expression' => '0 0 * * *',
            'is_active' => true,
        ]);

        $response->assertOk();

        $this->assertResponderUsed($response, 'cleanup-schedules');
        $this->assertResponseId($response, 'store');

        $schedule = CleanupSchedule::query()->where('name', 'Daily Cleanup')->firstOrFail();

        $this->assertTrue($schedule->is_active);
    }

    public function test_creates_inactive_cleanup_schedule(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('backup.schedules.cleanup.store'), [
            'name' => 'Inactive Cleanup',
            'cron_expression' => '0 1 * * *',
            'is_active' => false,
        ]);

        $response->assertOk();

        $schedule = CleanupSchedule::query()->where('name', 'Inactive Cleanup')->firstOrFail();

        $this->assertFalse($schedule->is_active);
    }

    public function test_sets_cleanup_schedule_as_inactive(): void
    {
        $admin = $this->createAdmin();

        $schedule = CleanupSchedule::create([
            'name' => 'Cleanup Toggle',
            'cron_expression' => '0 2 * * *',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('backup.schedules.cleanup.update', $schedule), [
            'is_active' => false,
        ]);

        $response->assertOk();

        $schedule->refresh();

        $this->assertFalse($schedule->is_active);
    }

    public function test_changes_cleanup_schedule_cron_expression(): void
    {
        $admin = $this->createAdmin();

        $schedule = CleanupSchedule::create([
            'name' => 'Cleanup Cron Switch',
            'cron_expression' => '0 3 * * *',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('backup.schedules.cleanup.update', $schedule), [
            'cron_expression' => '15 4 * * *',
        ]);

        $response->assertOk();

        $schedule->refresh();

        $this->assertSame('15 4 * * *', $schedule->cron_expression);
    }

    public function test_creates_cleanup_schedule_with_all_destinations(): void
    {
        $admin = $this->createAdmin();

        $destinationOne = FilesystemConfiguration::factory()->local()->create(['is_active' => true]);
        $destinationTwo = FilesystemConfiguration::factory()->ftp()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post(route('backup.schedules.cleanup.store'), [
            'name' => 'Cleanup With All Destinations',
            'cron_expression' => '0 0 * * *',
            'is_active' => true,
            'destination_ids' => [$destinationOne->id, $destinationTwo->id],
        ]);

        $response->assertOk();

        $schedule = CleanupSchedule::query()->where('name', 'Cleanup With All Destinations')->firstOrFail();

        $this->assertEqualsCanonicalizing(
            [$destinationOne->id, $destinationTwo->id],
            $schedule->filesystemConfigurations()->pluck('filesystem_configurations.id')->all(),
        );
    }

    public function test_creates_cleanup_schedule_for_single_destination(): void
    {
        $admin = $this->createAdmin();

        $destination = FilesystemConfiguration::factory()->local()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post(route('backup.schedules.cleanup.store'), [
            'name' => 'Cleanup With One Destination',
            'cron_expression' => '0 0 * * *',
            'is_active' => true,
            'destination_ids' => [$destination->id],
        ]);

        $response->assertOk();

        $schedule = CleanupSchedule::query()->where('name', 'Cleanup With One Destination')->firstOrFail();

        $this->assertEquals([$destination->id], $schedule->filesystemConfigurations()->pluck('filesystem_configurations.id')->all());
    }

    public function test_rejects_inactive_destination_on_create(): void
    {
        $admin = $this->createAdmin();

        $destination = FilesystemConfiguration::factory()->local()->create(['is_active' => false]);

        $response = $this->actingAs($admin)
            ->from(route('backup.schedules.cleanup.create'))
            ->post(route('backup.schedules.cleanup.store'), [
                'name' => 'Cleanup With Inactive Destination',
                'cron_expression' => '0 0 * * *',
                'is_active' => true,
                'destination_ids' => [$destination->id],
            ]);

        $response->assertRedirect(route('backup.schedules.cleanup.create'));
        $response->assertSessionHasErrors(['destination_ids.0']);

        $this->assertDatabaseMissing('cleanup_schedules', ['name' => 'Cleanup With Inactive Destination']);
    }

    public function test_rejects_empty_destination_ids_on_create(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->from(route('backup.schedules.cleanup.create'))
            ->post(route('backup.schedules.cleanup.store'), [
                'name' => 'Cleanup Without Destinations',
                'cron_expression' => '0 0 * * *',
                'is_active' => true,
                'destination_ids' => [],
            ]);

        $response->assertRedirect(route('backup.schedules.cleanup.create'));
        $response->assertSessionHasErrors(['destination_ids']);

        $this->assertDatabaseMissing('cleanup_schedules', ['name' => 'Cleanup Without Destinations']);
    }

    public function test_updates_cleanup_schedule_destinations(): void
    {
        $admin = $this->createAdmin();

        $original = FilesystemConfiguration::factory()->local()->create(['is_active' => true]);
        $replacement = FilesystemConfiguration::factory()->ftp()->create(['is_active' => true]);

        $schedule = CleanupSchedule::create([
            'name' => 'Cleanup Destination Swap',
            'cron_expression' => '0 0 * * *',
            'is_active' => true,
        ]);

        $schedule->filesystemConfigurations()->attach($original);

        $response = $this->actingAs($admin)->put(route('backup.schedules.cleanup.update', $schedule), [
            'destination_ids' => [$replacement->id],
        ]);

        $response->assertOk();

        $this->assertEquals([$replacement->id], $schedule->filesystemConfigurations()->pluck('filesystem_configurations.id')->all());
    }

    public function test_updating_cleanup_schedule_without_destinations_keeps_existing_ones(): void
    {
        $admin = $this->createAdmin();

        $destination = FilesystemConfiguration::factory()->local()->create(['is_active' => true]);

        $schedule = CleanupSchedule::create([
            'name' => 'Cleanup Keeps Destinations',
            'cron_expression' => '0 0 * * *',
            'is_active' => true,
        ]);

        $schedule->filesystemConfigurations()->attach($destination);

        $response = $this->actingAs($admin)->put(route('backup.schedules.cleanup.update', $schedule), [
            'is_active' => false,
        ]);

        $response->assertOk();

        $schedule->refresh();

        $this->assertFalse($schedule->is_active);
        $this->assertEquals([$destination->id], $schedule->filesystemConfigurations()->pluck('filesystem_configurations.id')->all());
    }

    public function test_rejects_unknown_destination_on_update(): void
    {
        $admin = $this->createAdmin();

        $schedule = CleanupSchedule::create([
            'name' => 'Cleanup Unknown Destination',
            'cron_expression' => '0 0 * * *',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('backup.schedules.cleanup.edit', $schedule))
            ->put(route('backup.schedules.cleanup.update', $schedule), [
                'destination_ids' => [99999],
            ]);

        $response->assertRedirect(route('backup.schedules.cleanup.edit', $schedule));
        $response->assertSessionHasErrors(['destination_ids.0']);

        $this->assertCount(0, $schedule->filesystemConfigurations()->get());
    }

    public function test_create_page_lists_active_destinations_ordered_by_name(): void
    {
        $admin = $this->createAdmin();

        FilesystemConfiguration::factory()->local()->create(['name' => 'Zulu Destination', 'is_active' => true]);
        FilesystemConfiguration::factory()->local()->create(['name' => 'Alpha Destination', 'is_active' => true]);
        FilesystemConfiguration::factory()->local()->create(['name' => 'Hidden Destination', 'is_active' => false]);

        $response = $this->actingAs($admin)->get(route('backup.schedules.cleanup.create'));

        $response->assertOk();

        $this->assertResponderUsed($response, 'cleanup-schedules');
        $this->assertResponseId($response, 'create');

        $this->assertSame(
            ['Alpha Destination', 'Zulu Destination'],
            collect($response->json('data.destinations'))->pluck('name')->all(),
        );
    }

    public function test_edit_page_lists_active_destinations(): void
    {
        $admin = $this->createAdmin();

        $attached = FilesystemConfiguration::factory()->local()->create(['is_active' => true]);
        $available = FilesystemConfiguration::factory()->ftp()->create(['is_active' => true]);

        $schedule = CleanupSchedule::create([
            'name' => 'Cleanup Edit Destinations',
            'cron_expression' => '0 0 * * *',
            'is_active' => true,
        ]);

        $schedule->filesystemConfigurations()->attach($attached);

        $response = $this->actingAs($admin)->get(route('backup.schedules.cleanup.edit', $schedule));

        $response->assertOk();

        $this->assertResponseId($response, 'edit');

        $this->assertEqualsCanonicalizing(
            [$attached->id, $available->id],
            collect($response->json('data.destinations'))->pluck('id')->all(),
        );
    }

    public function test_edit_page_lists_an_attached_destination_that_is_inactive(): void
    {
        $admin = $this->createAdmin();

        $deactivated = FilesystemConfiguration::factory()->local()->create(['is_active' => false]);

        $schedule = CleanupSchedule::create([
            'name' => 'Cleanup Inactive Destination',
            'cron_expression' => '0 0 * * *',
            'is_active' => true,
        ]);

        $schedule->filesystemConfigurations()->attach($deactivated);

        $response = $this->actingAs($admin)->get(route('backup.schedules.cleanup.edit', $schedule));

        $response->assertOk();

        // An attached destination stays visible after being deactivated so it is not
        // silently dropped the next time the schedule is saved.
        $this->assertEquals(
            [$deactivated->id],
            collect($response->json('data.destinations'))->pluck('id')->all(),
        );
    }

    public function test_edit_page_omits_inactive_destinations_that_are_not_attached(): void
    {
        $admin = $this->createAdmin();

        $active = FilesystemConfiguration::factory()->local()->create(['is_active' => true]);
        FilesystemConfiguration::factory()->ftp()->create(['is_active' => false]);

        $schedule = CleanupSchedule::create([
            'name' => 'Cleanup Unattached Inactive Destination',
            'cron_expression' => '0 0 * * *',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('backup.schedules.cleanup.edit', $schedule));

        $response->assertOk();

        $this->assertEquals(
            [$active->id],
            collect($response->json('data.destinations'))->pluck('id')->all(),
        );
    }

    public function test_removes_cleanup_schedule(): void
    {
        $admin = $this->createAdmin();

        $schedule = CleanupSchedule::create([
            'name' => 'Remove Cleanup',
            'cron_expression' => '0 5 * * *',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('backup.schedules.cleanup.destroy', $schedule));

        $response->assertOk();

        $this->assertResponderUsed($response, 'cleanup-schedules');
        $this->assertResponseId($response, 'destroy');

        $this->assertModelMissing($schedule);
    }
}
