<?php

namespace SameOldNick\BackupManager\Contracts\Responders;

use SameOldNick\BackupManager\Models\CleanupSchedule;

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
    public function renderStoreCleanupSchedule(CleanupSchedule $schedule);

    /**
     * Renders the edit cleanup schedule screen.
     *
     * @return mixed
     */
    public function renderEditCleanupSchedule(CleanupSchedule $schedule);

    /**
     * Renders the response after updating a cleanup schedule.
     *
     * @return mixed
     */
    public function renderUpdateCleanupSchedule(CleanupSchedule $schedule);

    /**
     * Renders the response after deleting a cleanup schedule.
     *
     * @return mixed
     */
    public function renderDestroyCleanupSchedule(CleanupSchedule $schedule);
}
