<?php

namespace VendorName\BackupManager\Responders;

use SameOldNick\BackupManager\Contracts\Responders\CleanupSchedulesUiResponder as CleanupSchedulesUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\DestroyCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\EditCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\StoreCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\UpdateCleanupScheduleViewData;

class CleanupSchedulesUiResponder implements CleanupSchedulesUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderCreateCleanupSchedule()
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderStoreCleanupSchedule(StoreCleanupScheduleViewData $data)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditCleanupSchedule(EditCleanupScheduleViewData $data)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateCleanupSchedule(UpdateCleanupScheduleViewData $data)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyCleanupSchedule(DestroyCleanupScheduleViewData $data)
    {
        //
    }
}
