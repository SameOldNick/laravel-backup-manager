<?php

namespace SameOldNick\BackupManager\Contracts\Responders;

use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Models\Collections\FilesystemConfigurationCollection;

interface BackupSchedulesUiResponder
{
    /**
     * Renders the create backup schedule screen.
     *
     * @return mixed
     */
    public function renderCreateBackupSchedule(FilesystemConfigurationCollection $configurations);

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
    public function renderEditBackupSchedule(BackupSchedule $schedule, FilesystemConfigurationCollection $configurations);

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
