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
        $request->validate([
            'type' => [
                'required',
                Rule::in(BackupTypes::acceptedValues()),
            ],
            'uuid' => [
                'required',
                'uuid',
            ],
        ]);

        $uuid = $request->str('uuid');
        $type = $request->str('type');
        $user = $request->user();

        $lease = $this->service->getBackupChannelLease($this->service->createChannelId($uuid));

        if ($lease === null) {
            abort(404, 'Backup channel not found');
        }

        if ($lease->notifiableKey !== (string) $user->getAuthIdentifier()) {
            abort(403, 'Unauthorized');
        }

        $this->service->dispatchBackupJob($lease, BackupTypes::fromValue($type), $user);

        return $this->ui->renderStartBackup(new StartBackupViewData(
            type: $type,
            uuid: $uuid,
            lease: $lease,
        ));
    }

    /**
     * Shows the perform backup page.
     */
    public function show(Request $request, string $type, string $uuid)
    {
        $lease = $this->service->getBackupChannelLease($this->service->createChannelId($uuid));

        return $this->ui->renderPerformBackup(new PerformBackupViewData(
            type: $type,
            uuid: $uuid,
            lease: $lease,
        ));
    }
}
