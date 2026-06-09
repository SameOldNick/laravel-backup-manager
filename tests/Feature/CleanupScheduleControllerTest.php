<?php

namespace SameOldNick\BackupManager\Tests\Feature;

use SameOldNick\BackupManager\Models\CleanupSchedule;
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
