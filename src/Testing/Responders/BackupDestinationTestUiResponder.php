<?php

namespace SameOldNick\BackupManager\Testing\Responders;

use SameOldNick\BackupManager\Contracts\Responders\BackupDestinationTestUiResponder as BackupDestinationTestUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinationTest\InitializeBackupDestinationTestViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinationTest\ShowBackupDestinationTestViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinationTest\StartBackupDestinationTestViewData;
use SameOldNick\BackupManager\Testing\Concerns;

class BackupDestinationTestUiResponder implements BackupDestinationTestUiResponderContract
{
    use Concerns\CreatesTestResponses;

    /**
     * {@inheritDoc}
     */
    public function renderInitializeBackupDestinationTest(InitializeBackupDestinationTestViewData $data)
    {
        return $this->createTestResponse('initialize', [
            'configuration' => $data->configuration,
            'uuid' => $data->uuid,
            'lease' => $data->lease,
            'startUrl' => url()->temporarySignedRoute('backup.destinations.test.start', $data->lease->expiresAt, [
                'destination' => $data->configuration,
            ]),
            'showUrl' => url()->temporarySignedRoute('backup.destinations.test.show', $data->lease->expiresAt, [
                'destination' => $data->configuration,
                'uuid' => $data->uuid,
            ]),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderStartBackupDestinationTest(StartBackupDestinationTestViewData $data)
    {
        return $this->createTestResponse('start', [
            'configuration' => $data->configuration,
            'uuid' => $data->uuid,
            'lease' => $data->lease,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderShowBackupDestinationTest(ShowBackupDestinationTestViewData $data)
    {
        return $this->createTestResponse('perform', [
            'configuration' => $data->configuration,
            'backupConfig' => $data->backupConfig,
            'uuid' => $data->uuid,
            'lease' => $data->lease,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function getSourceResponder(): string
    {
        return 'backup-destination-test';
    }
}
