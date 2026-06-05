<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use SameOldNick\BackupManager\Contracts\Responders\SchedulesUiResponder;
use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Models\CleanupSchedule;

class ScheduleController
{
    public function __construct(
        protected readonly SchedulesUiResponder $ui
    ) {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->ui->renderSchedulesList(BackupSchedule::all(), CleanupSchedule::all());
    }
}
