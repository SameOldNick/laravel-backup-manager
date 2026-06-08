<?php

namespace SameOldNick\BackupManager\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SameOldNick\BackupManager\DataTransferObjects\CreateBackupDestinationData;
use SameOldNick\BackupManager\Models\Collections\FilesystemConfigurationCollection;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Models\FilesystemConfigurationFTP;
use SameOldNick\BackupManager\Models\FilesystemConfigurationLocal;
use SameOldNick\BackupManager\Models\FilesystemConfigurationSFTP;

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

    public function createBackupDestination(CreateBackupDestinationData $data): FilesystemConfiguration
    {
        return DB::transaction(function () use ($data) {
            $config = match ($data->type) {
                CreateBackupDestinationData::TYPE_LOCAL => FilesystemConfigurationLocal::create([
                    'root' => $data->root,
                    'extra' => $data->extra,
                ]),
                CreateBackupDestinationData::TYPE_FTP => FilesystemConfigurationFTP::create([
                    'host' => $data->host,
                    'port' => $data->port,
                    'username' => $data->username,
                    'password' => $data->password,
                    'root' => $data->root,
                    'extra' => $data->extra,
                ]),
                CreateBackupDestinationData::TYPE_SFTP => FilesystemConfigurationSFTP::create([
                    'host' => $data->host,
                    'port' => $data->port,
                    'username' => $data->username,
                    'password' => $data->password,
                    'private_key' => $data->privateKey,
                    'passphrase' => $data->passphrase,
                    'root' => $data->root,
                    'extra' => $data->extra,
                ]),
            };

            $fsConfig = new FilesystemConfiguration([
                'name' => $data->name,
                'slug' => $data->slug ?? Str::slug($data->name),
                'disk_type' => $data->type,
                'is_active' => $data->enabled,
            ]);

            $fsConfig->configurable()->associate($config);
            $fsConfig->save();

            return $fsConfig;
        });
    }
}
