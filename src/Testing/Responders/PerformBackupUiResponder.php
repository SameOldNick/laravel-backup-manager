<?php

namespace SameOldNick\BackupManager\Testing\Responders;

use SameOldNick\BackupManager\Contracts\Responders\PerformBackupUiResponder as PerformBackupUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup\PerformBackupViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\PerformBackup\StartBackupViewData;
use SameOldNick\BackupManager\Testing\Concerns;

class PerformBackupUiResponder implements PerformBackupUiResponderContract
{
    use Concerns\CreatesTestResponses;

    /**
     * {@inheritDoc}
     */
    public function renderStartBackup(StartBackupViewData $data)
    {
        return $this->createTestResponse('start', [
            'type' => $data->type,
            'uuid' => $data->uuid,
            'lease' => $data->lease,
            'redirectUrl' => url()->temporarySignedRoute('backup.perform.show', now()->addMinutes(5), [
                'type' => $data->type,
                'uuid' => $data->uuid,
            ]),
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
