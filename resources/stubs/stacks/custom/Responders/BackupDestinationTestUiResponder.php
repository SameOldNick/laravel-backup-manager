<?php

namespace VendorName\BackupManager\Responders;

use SameOldNick\BackupManager\Contracts\Responders\BackupDestinationTestUiResponder as BackupDestinationTestUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinationTest\InitializeBackupDestinationTestViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinationTest\ShowBackupDestinationTestViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinationTest\StartBackupDestinationTestViewData;

class BackupDestinationTestUiResponder implements BackupDestinationTestUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderInitializeBackupDestinationTest(InitializeBackupDestinationTestViewData $data)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderStartBackupDestinationTest(StartBackupDestinationTestViewData $data)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderShowBackupDestinationTest(ShowBackupDestinationTestViewData $data)
    {
        //
    }
}
