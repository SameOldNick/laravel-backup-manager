<?php

namespace SameOldNick\BackupManager\Http\Responders\Tests;

use SameOldNick\BackupManager\Contracts\Responders\CleanupSchedulesUiResponder as CleanupSchedulesUiResponderContract;
use SameOldNick\BackupManager\Models\CleanupSchedule;

class CleanupSchedulesUiResponder implements CleanupSchedulesUiResponderContract
{
    use Concerns\CreatesTestResponses;

    /**
     * {@inheritDoc}
     */
    public function renderCreateCleanupSchedule()
    {
        return $this->createTestResponse('create');
    }

    /**
     * {@inheritDoc}
     */
    public function renderStoreCleanupSchedule(CleanupSchedule $schedule)
    {
        return $this->createTestResponse('store', [
            'schedule' => $schedule,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditCleanupSchedule(CleanupSchedule $schedule)
    {
        return $this->createTestResponse('edit', [
            'schedule' => $schedule,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateCleanupSchedule(CleanupSchedule $schedule)
    {
        return $this->createTestResponse('update', [
            'schedule' => $schedule,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyCleanupSchedule(CleanupSchedule $schedule)
    {
        return $this->createTestResponse('destroy', [
            'schedule' => $schedule,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function getSourceResponder(): string
    {
        return 'cleanup-schedules';
    }
}
