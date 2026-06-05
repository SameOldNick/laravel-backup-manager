<?php

namespace SameOldNick\BackupManager\Contracts\Responders;

use Illuminate\Database\Eloquent\Collection;
use SameOldNick\BackupManager\Models\BackupSchedule;

interface BackupSchedulesUiResponder
{
    /**
     * Renders the create backup schedule screen.
     *
     * @return mixed
     */
    public function renderCreateBackupSchedule(Collection $configurations);

    /**
     * Renders the response after storing a backup schedule.
     *
     * @return mixed
     */
    public function renderStoreBackupSchedule(BackupSchedule $schedule);

    /**
     * Renders the edit backup schedule screen.
     *
     * @return mixed
     */
    public function renderEditBackupSchedule(BackupSchedule $schedule, Collection $destinations);

    /**
     * Renders the response after updating a backup schedule.
     *
     * @return mixed
     */
    public function renderUpdateBackupSchedule(BackupSchedule $schedule);

    /**
     * Renders the response after deleting a backup schedule.
     *
     * @return mixed
     */
    public function renderDestroyBackupSchedule(BackupSchedule $schedule);
}
