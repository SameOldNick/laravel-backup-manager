<?php

namespace VendorName\BackupManager\Responders;

use SameOldNick\BackupManager\Contracts\Responders\CleanupSchedulesUiResponder as CleanupSchedulesUiResponderContract;
use SameOldNick\BackupManager\Models\CleanupSchedule;

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
    public function renderStoreCleanupSchedule(CleanupSchedule $schedule)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditCleanupSchedule(CleanupSchedule $schedule)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateCleanupSchedule(CleanupSchedule $schedule)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyCleanupSchedule(CleanupSchedule $schedule)
    {
        //
    }
}
