<?php

namespace SameOldNick\BackupManager\Http\Responders\Tests;

use Illuminate\Pagination\AbstractPaginator;
use SameOldNick\BackupManager\Contracts\Responders\BackupsUiResponder as BackupsUiResponderContract;

class BackupsUiResponder implements BackupsUiResponderContract
{
    use Concerns\CreatesTestResponses;

    /**
     * {@inheritDoc}
     */
    public function renderBackupsList(AbstractPaginator $backups)
    {
        return $this->createTestResponse('list', [
            'backups' => $backups,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderPerformBackup(string $type, string $uuid)
    {
        return $this->createTestResponse('perform', [
            'type' => $type,
            'uuid' => $uuid,
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
