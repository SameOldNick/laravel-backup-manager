<?php

namespace SameOldNick\BackupManager\Testing\Responders;

use SameOldNick\BackupManager\Contracts\Responders\BackupsUiResponder as BackupsUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Backups\BackupsListViewData;
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
            'paginated' => $data->backups->paginate(request()->input('per_page', 15), request()->integer('page', 1))->toArray(),
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
