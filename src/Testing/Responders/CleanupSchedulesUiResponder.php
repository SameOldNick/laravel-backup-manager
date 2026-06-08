<?php

namespace SameOldNick\BackupManager\Testing\Responders;

use SameOldNick\BackupManager\Contracts\Responders\CleanupSchedulesUiResponder as CleanupSchedulesUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\DestroyCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\EditCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\StoreCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\UpdateCleanupScheduleViewData;
use SameOldNick\BackupManager\Testing\Concerns;

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
    public function renderStoreCleanupSchedule(StoreCleanupScheduleViewData $data)
    {
        return $this->createTestResponse('store', [
            'schedule' => $data->schedule,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditCleanupSchedule(EditCleanupScheduleViewData $data)
    {
        return $this->createTestResponse('edit', [
            'schedule' => $data->schedule,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateCleanupSchedule(UpdateCleanupScheduleViewData $data)
    {
        return $this->createTestResponse('update', [
            'schedule' => $data->schedule,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyCleanupSchedule(DestroyCleanupScheduleViewData $data)
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
        return 'cleanup-schedules';
    }
}
