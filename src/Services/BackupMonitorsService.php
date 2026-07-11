<?php

namespace SameOldNick\BackupManager\Services;

use Illuminate\Support\Facades\DB;
use SameOldNick\BackupManager\DataTransferObjects\Services\CreateBackupMonitorData;
use SameOldNick\BackupManager\DataTransferObjects\Services\UpdateBackupMonitorData;
use SameOldNick\BackupManager\Models\BackupMonitor;
use SameOldNick\BackupManager\Models\Collections\BackupMonitorCollection;

class BackupMonitorsService
{
    public function __construct()
    {
        //
    }

    public function getBackupMonitors(?bool $active = null, ?string $query = null): BackupMonitorCollection
    {
        $monitorQuery = BackupMonitor::query();

        if ($active !== null) {
            $monitorQuery->where('is_active', $active);
        }

        if ($query) {
            $monitorQuery->where('name', 'like', "%{$query}%");
        }

        return new BackupMonitorCollection($monitorQuery->latest()->get());
    }

    public function createBackupMonitor(CreateBackupMonitorData $data): BackupMonitor
    {
        return DB::transaction(function () use ($data) {
            return BackupMonitor::create([
                'name' => $data->name,
                'disks' => $data->disks,
                'maximum_age_in_days' => $data->maximumAgeInDays,
                'maximum_storage_in_megabytes' => $data->maximumStorageInMegabytes,
                'is_active' => $data->enabled,
            ]);
        });
    }

    public function updateBackupMonitor(BackupMonitor $monitor, UpdateBackupMonitorData $data): BackupMonitor
    {
        return DB::transaction(function () use ($monitor, $data) {
            if ($data->name !== null) {
                $monitor->name = $data->name;
            }
            if ($data->disks !== null) {
                $monitor->disks = $data->disks;
            }
            if ($data->maximumAgeInDays !== null) {
                $monitor->maximum_age_in_days = $data->maximumAgeInDays;
            }
            if ($data->maximumStorageInMegabytes !== null) {
                $monitor->maximum_storage_in_megabytes = $data->maximumStorageInMegabytes;
            }
            if ($data->enabled !== null) {
                $monitor->is_active = $data->enabled;
            }

            if ($monitor->isDirty()) {
                $monitor->save();
            }

            return $monitor;
        });
    }

    public function removeBackupMonitor(BackupMonitor $monitor): void
    {
        $monitor->delete();
    }
}