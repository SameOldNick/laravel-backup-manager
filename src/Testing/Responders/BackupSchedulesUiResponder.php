<?php

namespace SameOldNick\BackupManager\Testing\Responders;

use SameOldNick\BackupManager\Contracts\Responders\BackupSchedulesUiResponder as BackupSchedulesUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CreateBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\DestroyBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\EditBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\StoreBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\UpdateBackupScheduleViewData;
use SameOldNick\BackupManager\Testing\Concerns;

class BackupSchedulesUiResponder implements BackupSchedulesUiResponderContract
{
    use Concerns\CreatesTestResponses;

    /**
     * {@inheritDoc}
     */
    public function renderCreateBackupSchedule(CreateBackupScheduleViewData $data)
    {
        return $this->createTestResponse('create', [
            'configurations' => $data->configurations,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderStoreBackupSchedule(StoreBackupScheduleViewData $data)
    {
        return $this->createTestResponse('store', [
            'schedule' => $data->schedule,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditBackupSchedule(EditBackupScheduleViewData $data)
    {
        return $this->createTestResponse('edit', [
            'schedule' => $data->schedule,
            'destinations' => $data->configurations,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateBackupSchedule(UpdateBackupScheduleViewData $data)
    {
        return $this->createTestResponse('update', [
            'schedule' => $data->schedule,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyBackupSchedule(DestroyBackupScheduleViewData $data)
    {
        return $this->createTestResponse('destroy', [
            'schedule' => $data->schedule,
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
