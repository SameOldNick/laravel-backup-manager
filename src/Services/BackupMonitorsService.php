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
        $monitorQuery = BackupMonitor::query()
            ->when($active !== null, fn ($q) => $q->where('is_active', $active))
            ->when($query, fn ($q) => $q->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            }));

        return new BackupMonitorCollection($monitorQuery->latest()->get());
    }

    public function createBackupMonitor(CreateBackupMonitorData $data): BackupMonitor
    {
        return DB::transaction(function () use ($data) {
            $monitor = BackupMonitor::create([
                'name' => $data->name,
                'maximum_age_in_days' => $data->maximumAgeInDays,
                'maximum_storage_in_megabytes' => $data->maximumStorageInMegabytes,
                'is_active' => $data->enabled,
            ]);

            if ($data->destinationIds) {
                $monitor->filesystemConfigurations()->sync($data->destinationIds);
            }

            return $monitor;
        });
    }

    public function updateBackupMonitor(BackupMonitor $monitor, UpdateBackupMonitorData $data): BackupMonitor
    {
        return DB::transaction(function () use ($monitor, $data) {
            if ($data->name !== null) {
                $monitor->name = $data->name;
            }
            if ($data->destinationIds !== null) {
                $monitor->filesystemConfigurations()->sync($data->destinationIds);
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
