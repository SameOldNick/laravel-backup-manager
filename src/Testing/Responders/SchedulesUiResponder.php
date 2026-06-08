<?php

namespace SameOldNick\BackupManager\Testing\Responders;

use SameOldNick\BackupManager\Contracts\Responders\SchedulesUiResponder as SchedulesUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\SchedulesListViewData;
use SameOldNick\BackupManager\Testing\Concerns;

class SchedulesUiResponder implements SchedulesUiResponderContract
{
    use Concerns\CreatesTestResponses;

    /**
     * {@inheritDoc}
     */
    public function renderSchedulesList(SchedulesListViewData $data)
    {
        return $this->createTestResponse('list', [
            'backupSchedules' => $data->backupSchedules,
            'cleanupSchedules' => $data->cleanupSchedules,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function getSourceResponder(): string
    {
        return 'schedules';
    }
}
