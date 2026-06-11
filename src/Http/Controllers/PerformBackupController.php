<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use SameOldNick\BackupManager\Contracts\Responders\PerformBackupUiResponder;
use SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup\InitializeBackupViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup\PerformBackupViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup\StartBackupViewData;
use SameOldNick\BackupManager\Enums\BackupTypes;
use SameOldNick\BackupManager\Services\PerformBackupService;

class PerformBackupController
{
    public function __construct(
        protected readonly PerformBackupService $service,
        protected readonly PerformBackupUiResponder $ui
    ) {
        //
    }

    /**
     * Performs a backup
     */
    public function initialize(Request $request)
    {
        $request->validate([
            'type' => [
                'required',
                Rule::in(BackupTypes::acceptedValues()),
            ],
        ]);

        /**
         * The backup is run using a job.
         * This allows it to run asynchronously (so websocket can handle it).
         */
        $uuid = Str::uuid();

        $type = BackupTypes::fromValue((string) $request->str('type'));

        $lease = $this->service->openBackupChannel(
            user: $request->user(),
            uuid: $uuid,
        );

        return $this->ui->renderInitializeBackup(new InitializeBackupViewData(
            type: $type,
            uuid: $uuid,
            lease: $lease,
        ));
    }

    /**
     * Starts the backup job.
     */
    public function start(Request $request)
    {
        $type = (string) $request->str('type');
        $uuid = (string) $request->str('uuid');
        $user = $request->user();

        if (! BackupTypes::fromValue($type)) {
            abort(400, 'Invalid backup type');
        }

        if (! Str::isUuid($uuid)) {
            abort(400, 'Invalid UUID');
        }

        $lease = $this->service->getChannelLease($this->service->createChannelId($uuid));

        if ($lease === null) {
            abort(404, 'Backup channel not found');
        }

        if ($lease->notifiableClass !== $user::class || $lease->notifiableKey !== (string) $user->getAuthIdentifier()) {
            abort(403, 'Unauthorized');
        }

        $started = $this->service->dispatchBackupJobOnce($lease, BackupTypes::fromValue($type), $user, $uuid);

        if ($started === null) {
            abort(409, 'Backup has already been started for this channel');
        }

        return $this->ui->renderStartBackup(new StartBackupViewData(
            type: $type,
            uuid: $uuid,
            lease: $lease,
            backupRun: $started,
        ));
    }

    /**
     * Shows the perform backup page.
     */
    public function show(Request $request, string $type, string $uuid)
    {
        $lease = $this->service->getChannelLease($this->service->createChannelId($uuid));

        return $this->ui->renderPerformBackup(new PerformBackupViewData(
            type: $type,
            uuid: $uuid,
            lease: $lease,
        ));
    }
}
