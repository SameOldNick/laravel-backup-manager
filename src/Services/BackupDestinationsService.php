<?php

namespace SameOldNick\BackupManager\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SameOldNick\BackupManager\Broadcasting\Access\ChannelAccessManager;
use SameOldNick\BackupManager\Broadcasting\Access\ChannelLease;
use SameOldNick\BackupManager\DataTransferObjects\Services\CreateBackupDestinationData;
use SameOldNick\BackupManager\DataTransferObjects\Services\UpdateBackupDestinationData;
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
        $fsConfigQuery = FilesystemConfiguration::query();

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

    public function updateBackupDestination(FilesystemConfiguration $destination, UpdateBackupDestinationData $data): FilesystemConfiguration
    {
        return DB::transaction(function () use ($destination, $data) {
            if ($data->enabled !== null) {
                $destination->is_active = (bool) $data->enabled;
            }

            if ($data->name !== null) {
                $destination->name = $data->name;
            }

            if ($data->slug !== null) {
                $destination->slug = $data->slug;
            }

            if ($destination->isDirty()) {
                $destination->save();
            }

            /** @var FilesystemConfigurationLocal|FilesystemConfigurationFTP|FilesystemConfigurationSFTP $configurable */
            $configurable = $destination->configurable;
            $diskType = $destination->disk_type;

            if ($diskType === 'local') {
                if ($data->root !== null) {
                    $configurable->root = $data->root;
                }

                $configurable->extra = $data->extra ?? $configurable->extra;
            } elseif ($diskType === 'ftp') {
                if ($data->password !== null) {
                    $configurable->password = $data->password;
                }

                if ($data->root !== null) {
                    $configurable->root = $data->root;
                }

                $configurable->host = $data->host ?? $configurable->host;
                $configurable->port = $data->port ?? $configurable->port;
                $configurable->username = $data->username ?? $configurable->username;
                $configurable->extra = $data->extra ?? $configurable->extra;
            } elseif ($diskType === 'sftp') {
                if ($data->password !== null && $data->authType === UpdateBackupDestinationData::AUTH_TYPE_PASSWORD) {
                    $configurable->private_key = null;
                    $configurable->passphrase = null;
                    $configurable->password = $data->password;
                } elseif ($data->privateKey !== null && $data->authType === UpdateBackupDestinationData::AUTH_TYPE_KEY) {
                    $configurable->password = null;
                    $configurable->private_key = $data->privateKey;
                    $configurable->passphrase = $data->passphrase;
                }

                if ($data->root !== null) {
                    $configurable->root = $data->root;
                }

                $configurable->host = $data->host ?? $configurable->host;
                $configurable->port = $data->port ?? $configurable->port;
                $configurable->username = $data->username ?? $configurable->username;
                $configurable->extra = $data->extra ?? $configurable->extra;
            }

            $configurable->save();

            return $destination;
        });
    }

    public function removeBackupDestination(FilesystemConfiguration $destination): void
    {
        $destination->configurable->delete();
        $destination->delete();
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
