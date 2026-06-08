<?php

namespace SameOldNick\BackupManager\Contracts\Responders;

use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\CreateBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\DestroyBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\EditBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\StoreBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\UpdateBackupScheduleViewData;

interface BackupSchedulesUiResponder
{
    /**
     * Renders the create backup schedule screen.
     *
     * @return mixed
     */
    public function renderCreateBackupSchedule(CreateBackupScheduleViewData $data);

    /**
     * Renders the response after storing a backup schedule.
     *
     * @return mixed
     */
    public function renderStoreBackupSchedule(StoreBackupScheduleViewData $data);

    /**
     * Renders the edit backup schedule screen.
     *
     * @return mixed
     */
    public function renderEditBackupSchedule(EditBackupScheduleViewData $data);

    /**
     * Renders the response after updating a backup schedule.
     *
     * @return mixed
     */
    public function renderUpdateBackupSchedule(UpdateBackupScheduleViewData $data);

    /**
     * Renders the response after deleting a backup schedule.
     *
     * @return mixed
     */
    public function renderDestroyBackupSchedule(DestroyBackupScheduleViewData $data);
}
