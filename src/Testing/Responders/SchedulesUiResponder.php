<?php

namespace SameOldNick\BackupManager\Testing\Responders;

use Illuminate\Database\Eloquent\Collection;
use SameOldNick\BackupManager\Contracts\Responders\SchedulesUiResponder as SchedulesUiResponderContract;
use SameOldNick\BackupManager\Testing\Concerns;

class SchedulesUiResponder implements SchedulesUiResponderContract
{
    use Concerns\CreatesTestResponses;

    /**
     * {@inheritDoc}
     */
    public function renderSchedulesList(Collection $backupSchedules, Collection $cleanupSchedules)
    {
        return $this->createTestResponse('list', [
            'backupSchedules' => $backupSchedules,
            'cleanupSchedules' => $cleanupSchedules,
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
