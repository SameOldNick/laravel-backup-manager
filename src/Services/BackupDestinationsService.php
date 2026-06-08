<?php

namespace SameOldNick\BackupManager\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SameOldNick\BackupManager\Broadcasting\Access\ChannelAccessManager;
use SameOldNick\BackupManager\Broadcasting\Access\ChannelLease;
use SameOldNick\BackupManager\DataTransferObjects\CreateBackupDestinationData;
use SameOldNick\BackupManager\Jobs\Notifiable\FilesystemConfigurationTestJob;
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

    public function startBackupDestinationTest(FilesystemConfiguration $destination, ?string $uuid = null): ChannelLease
    {
        $channel = $this->createChannelId($uuid ?? Str::uuid());
        $user = request()->user();

        $lease = $this->openBackupDestinationTestChannelLease($channel, $user);

        dispatch(new FilesystemConfigurationTestJob($channel, $user, $destination->driver_name));

        return $lease;
    }

    public function createChannelId(string $uuid): string
    {
        return app(ChannelAccessManager::class)->createChannelId('test-destination', $uuid);
    }

    public function openBackupDestinationTestChannelLease(string $channel, object $user): ChannelLease
    {
        return app(ChannelAccessManager::class)->open(
            channelId: $channel,
            notifiable: $user,
            expiresAt: now()->addHours(3),
        );
    }
}
