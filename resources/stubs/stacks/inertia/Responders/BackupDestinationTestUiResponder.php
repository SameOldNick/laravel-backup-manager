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
        return redirect()->temporarySignedRoute('backup.destinations.test.show', $data->lease->expiresAt, [
            'destination' => $data->configuration,
            'uuid' => $data->uuid,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderStartBackupDestinationTest(StartBackupDestinationTestViewData $data)
    {
        return [
            'message' => __('backup::messages.backup_destination_test_started'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function renderShowBackupDestinationTest(ShowBackupDestinationTestViewData $data)
    {
        $startUrl = url()->temporarySignedRoute('backup.destinations.test.start', $data->lease->expiresAt, [
            'destination' => $data->configuration,
        ]);

        return inertia('dashboard/settings/backups/page', [
            'tab' => 'destinations',
            'action' => 'edit',
            'destination' => $data->configuration,
            'enabled' => $data->configuration->isEnabled($data->backupConfig),
            'test' => [
                'uuid' => $data->uuid,
                'start_url' => $startUrl,
            ],
        ]);
    }
}
