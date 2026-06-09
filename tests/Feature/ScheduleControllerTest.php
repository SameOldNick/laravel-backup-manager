<?php

namespace SameOldNick\BackupManager\Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;
use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Models\CleanupSchedule;
use SameOldNick\BackupManager\Testing\Concerns;
use SameOldNick\BackupManager\Tests\TestCase;

class ScheduleControllerTest extends TestCase
{
    use Concerns\UiResponderAssertions;

    public function test_displays_all_backup_and_cleanup_schedules(): void
    {
        $admin = $this->createAdmin();

        BackupSchedule::create([
            'name' => 'Weekly Files Backup',
            'type' => 'files',
            'cron_expression' => '0 0 * * 0',
            'is_active' => true,
        ]);

        CleanupSchedule::create([
            'name' => 'Weekly Cleanup',
            'cron_expression' => '0 1 * * 0',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('backup.schedules.index'));

        $response->assertOk();

        $this->assertResponderUsed($response, 'schedules');
        $this->assertResponseId($response, 'list');
        $this->assertResponseData($response, fn (AssertableJson $json) => $json
            ->count('backupSchedules', 1)
            ->count('cleanupSchedules', 1),
            interacted: false
        );
    }
}
