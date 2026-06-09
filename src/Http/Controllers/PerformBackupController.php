<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use SameOldNick\BackupManager\Contracts\Responders\PerformBackupUiResponder;
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
    public function start(Request $request)
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

        $lease = $this->service->startBackup(
            type: $type,
            user: $request->user(),
            uuid: $uuid,
        );

        return $this->ui->renderStartBackup(new StartBackupViewData(
            type: $type,
            uuid: $uuid,
            lease: $lease,
        ));
    }

    /**
     * Shows the perform backup page.
     */
    public function show(string $type, string $uuid)
    {
        $lease = $this->service->getBackupChannelLease($this->service->createChannelId($uuid));

        return $this->ui->renderPerformBackup(new PerformBackupViewData(
            type: $type,
            uuid: $uuid,
            lease: $lease,
        ));
    }
}
