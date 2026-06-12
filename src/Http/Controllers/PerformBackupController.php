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
    /**
     * PerformBackupController constructor.
     *
     * @param  PerformBackupService  $service  The service responsible for handling backup operations
     * @param  PerformBackupUiResponder  $ui  The UI responder responsible for rendering responses for backup operations
     */
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
        $validated = $request->validate([
            'type' => [
                'required',
                Rule::in(BackupTypes::acceptedValues()),
            ],
            'uuid' => [
                'required',
                'string',
                'uuid',
            ],
        ]);

        $type = (string) $validated['type'];
        $uuid = (string) $validated['uuid'];
        $user = $request->user();

        try {
            $run = $this->service->dispatchBackupJobOnce(BackupTypes::fromValue($type), $user, $uuid);

            return $this->ui->renderStartBackup(new StartBackupViewData(
                type: $type,
                uuid: $uuid,
                lease: $this->service->getBackupChannelLease($uuid),
                backupRun: $run,
            ));
        } catch (\Throwable $e) {
            abort(500, 'Failed to start backup job: '.$e->getMessage());
        }

    }

    /**
     * Shows the perform backup page.
     */
    public function show(Request $request, string $type, string $uuid)
    {
        $lease = $this->service->getBackupChannelLease($uuid);

        return $this->ui->renderPerformBackup(new PerformBackupViewData(
            type: $type,
            uuid: $uuid,
            lease: $lease,
        ));
    }
}
