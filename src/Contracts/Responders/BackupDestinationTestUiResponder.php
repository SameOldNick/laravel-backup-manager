<?php

namespace SameOldNick\BackupManager\Contracts\Responders;

use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinationTest\InitializeBackupDestinationTestViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinationTest\ShowBackupDestinationTestViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinationTest\StartBackupDestinationTestViewData;

interface BackupDestinationTestUiResponder
{
    /**
     * Renders the initialize backup destination test screen.
     *
     * @return mixed
     */
    public function renderInitializeBackupDestinationTest(InitializeBackupDestinationTestViewData $data);

    /**
     * Renders the start backup destination test screen.
     *
     * @return mixed
     */
    public function renderStartBackupDestinationTest(StartBackupDestinationTestViewData $data);

    /**
     * Renders the show backup destination test screen.
     *
     * @return mixed
     */
    public function renderShowBackupDestinationTest(ShowBackupDestinationTestViewData $data);
}
