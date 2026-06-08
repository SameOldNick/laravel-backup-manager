<?php

namespace SameOldNick\BackupManager\Services;

use Illuminate\Support\Facades\DB;
use SameOldNick\BackupManager\Models\Collections\FilesystemConfigurationCollection;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;

class BackupDestinationsService
{
    /**
     * Initializes BackupsService instance.
     */
    public function __construct()
    {
        //
    }

    public function getBackupDestinations(?bool $active = null, ?string $query = null): FilesystemConfigurationCollection
    {
        $fsConfigQuery = FilesystemConfiguration::query()->with('file');

        if ($active !== null) {
            $fsConfigQuery->where('is_active', $active);
        }

        if ($query) {
            $fsConfigQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('type', 'like', "%{$query}%")
                    ->orWhere('host', 'like', "%{$query}%");
            });
        }

        return new FilesystemConfigurationCollection($fsConfigQuery->latest()->get());
    }

}
