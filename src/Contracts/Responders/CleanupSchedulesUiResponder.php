<?php

namespace SameOldNick\BackupManager\Contracts\Responders;

use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\DestroyCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\EditCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\StoreCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\UpdateCleanupScheduleViewData;

interface CleanupSchedulesUiResponder
{
    /**
     * Renders the create cleanup schedule screen.
     *
     * @return mixed
     */
    public function renderCreateCleanupSchedule();

    /**
     * Renders the response after storing a cleanup schedule.
     *
     * @return mixed
     */
    public function renderStoreCleanupSchedule(StoreCleanupScheduleViewData $data);

    /**
     * Renders the edit cleanup schedule screen.
     *
     * @return mixed
     */
    public function renderEditCleanupSchedule(EditCleanupScheduleViewData $data);

    /**
     * Renders the response after updating a cleanup schedule.
     *
     * @return mixed
     */
    public function renderUpdateCleanupSchedule(UpdateCleanupScheduleViewData $data);

    /**
     * Renders the response after deleting a cleanup schedule.
     *
     * @return mixed
     */
    public function renderDestroyCleanupSchedule(DestroyCleanupScheduleViewData $data);
}
