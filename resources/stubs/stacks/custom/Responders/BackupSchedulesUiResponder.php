<?php

namespace VendorName\BackupManager\Responders;

use SameOldNick\BackupManager\Contracts\Responders\BackupSchedulesUiResponder as BackupSchedulesUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\CreateBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\DestroyBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\EditBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\StoreBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\UpdateBackupScheduleViewData;

class BackupSchedulesUiResponder implements BackupSchedulesUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderCreateBackupSchedule(CreateBackupScheduleViewData $data)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderStoreBackupSchedule(StoreBackupScheduleViewData $data)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditBackupSchedule(EditBackupScheduleViewData $data)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateBackupSchedule(UpdateBackupScheduleViewData $data)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyBackupSchedule(DestroyBackupScheduleViewData $data)
    {
        //
    }
}
