<?php

namespace SameOldNick\BackupManager\Testing\Responders;

use SameOldNick\BackupManager\Contracts\Responders\PerformBackupUiResponder as PerformBackupUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup\InitializeBackupViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup\PerformBackupViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup\StartBackupViewData;
use SameOldNick\BackupManager\Testing\Concerns;

class PerformBackupUiResponder implements PerformBackupUiResponderContract
{
    use Concerns\CreatesTestResponses;

    /**
     * {@inheritDoc}
     */
    public function renderInitializeBackup(InitializeBackupViewData $data)
    {
        return $this->createTestResponse('initialize', [
            'type' => $data->type,
            'uuid' => $data->uuid,
            'lease' => $data->lease,
            'startUrl' => url()->temporarySignedRoute('backup.perform.start', $data->lease->expiresAt, []),
            'showUrl' => url()->temporarySignedRoute('backup.perform.show', $data->lease->expiresAt, [
                'type' => $data->type,
                'uuid' => $data->uuid,
            ]),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderStartBackup(StartBackupViewData $data)
    {
        return $this->createTestResponse('start', [
            'type' => $data->type,
            'uuid' => $data->uuid,
            'lease' => $data->lease,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderPerformBackup(PerformBackupViewData $data)
    {
        return $this->createTestResponse('perform', [
            'type' => $data->type,
            'uuid' => $data->uuid,
            'lease' => $data->lease,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function getSourceResponder(): string
    {
        return 'perform';
    }
}
