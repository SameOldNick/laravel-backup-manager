<?php

namespace VendorName\BackupManager\Responders;

use Illuminate\Database\Eloquent\Collection;
use SameOldNick\BackupManager\Contracts\Responders\BackupSchedulesUiResponder as BackupSchedulesUiResponderContract;
use SameOldNick\BackupManager\Models\BackupSchedule;

class BackupSchedulesUiResponder implements BackupSchedulesUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderCreateBackupSchedule(Collection $configurations)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderStoreBackupSchedule(BackupSchedule $schedule)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditBackupSchedule(BackupSchedule $schedule, Collection $destinations)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateBackupSchedule(BackupSchedule $schedule)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyBackupSchedule(BackupSchedule $schedule)
    {
        //
    }
}
