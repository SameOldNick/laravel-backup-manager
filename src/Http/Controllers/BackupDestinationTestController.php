<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SameOldNick\BackupManager\Contracts\Responders\BackupDestinationTestUiResponder;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinationTest\InitializeBackupDestinationTestViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinationTest\ShowBackupDestinationTestViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinationTest\StartBackupDestinationTestViewData;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Services\BackupDestinationTestService;
use Spatie\Backup\Config\Config;

class BackupDestinationTestController
{
    public function __construct(
        protected readonly BackupDestinationTestService $service,
        protected readonly BackupDestinationTestUiResponder $ui
    ) {
        //
    }

    /**
     * Initializes a backup destination test.
     *
     * @return mixed
     */
    public function initialize(Request $request, FilesystemConfiguration $destination)
    {
        $uuid = Str::uuid();

        $lease = $this->service->openBackupDestinationTestChannel($uuid, $request->user());

        return $this->ui->renderInitializeBackupDestinationTest(new InitializeBackupDestinationTestViewData(
            configuration: $destination,
            uuid: $uuid,
            lease: $lease,
        ));
    }

    /**
     * Starts a backup destination test.
     *
     * @return mixed
     */
    public function start(Request $request, FilesystemConfiguration $destination)
    {
        $uuid = (string) $request->str('uuid');
        $user = $request->user();

        if (! Str::isUuid($uuid)) {
            abort(400, 'Invalid UUID');
        }

        try {
            $this->service->startBackupDestinationTest($destination, $uuid, $user);

            return $this->ui->renderStartBackupDestinationTest(new StartBackupDestinationTestViewData(
                configuration: $destination,
                uuid: $uuid,
                lease: $this->service->getBackupDestinationTestChannelLease($uuid),
            ));
        } catch (\Exception $e) {
            abort(500, 'Failed to start backup destination test: '.$e->getMessage());
        }

    }

    /**
     * Show the result of a test.
     *
     * @return mixed
     */
    public function show(Config $backupConfig, FilesystemConfiguration $destination, string $uuid)
    {
        return $this->ui->renderShowBackupDestinationTest(new ShowBackupDestinationTestViewData(
            configuration: $destination,
            uuid: $uuid,
            lease: $this->service->getBackupDestinationTestChannelLease($uuid),
            backupConfig: $backupConfig,
        ));
    }
}
