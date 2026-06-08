<?php

namespace SameOldNick\BackupManager\Testing\Responders;

use SameOldNick\BackupManager\Contracts\Responders\BackupsUiResponder as BackupsUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Backups\BackupsListViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Backups\PerformBackupViewData;
use SameOldNick\BackupManager\Testing\Concerns;

class BackupsUiResponder implements BackupsUiResponderContract
{
    use Concerns\CreatesTestResponses;

    /**
     * {@inheritDoc}
     */
    public function renderBackupsList(BackupsListViewData $data)
    {
        return $this->createTestResponse('list', [
            'backups' => $data->backups,
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
        return 'backups';
    }
}
