<?php

namespace SameOldNick\BackupManager\Testing\Responders;

use Illuminate\Database\Eloquent\Collection;
use SameOldNick\BackupManager\Contracts\Responders\BackupSchedulesUiResponder as BackupSchedulesUiResponderContract;
use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Testing\Concerns;

class BackupSchedulesUiResponder implements BackupSchedulesUiResponderContract
{
    use Concerns\CreatesTestResponses;

    /**
     * {@inheritDoc}
     */
    public function renderCreateBackupSchedule(Collection $configurations)
    {
        return $this->createTestResponse('create', [
            'configurations' => $configurations,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderStoreBackupSchedule(BackupSchedule $schedule)
    {
        return $this->createTestResponse('store', [
            'schedule' => $schedule,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditBackupSchedule(BackupSchedule $schedule, Collection $destinations)
    {
        return $this->createTestResponse('edit', [
            'schedule' => $schedule,
            'destinations' => $destinations,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateBackupSchedule(BackupSchedule $schedule)
    {
        return $this->createTestResponse('update', [
            'schedule' => $schedule,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyBackupSchedule(BackupSchedule $schedule)
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
        return 'backup-schedules';
    }
}
