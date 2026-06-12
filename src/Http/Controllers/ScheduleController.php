<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use SameOldNick\BackupManager\Contracts\Responders\SchedulesUiResponder;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\SchedulesListViewData;
use SameOldNick\BackupManager\Services\BackupSchedulesService;
use SameOldNick\BackupManager\Services\CleanupSchedulesService;

class ScheduleController
{
    /**
     * ScheduleController constructor.
     *
     * @param  BackupSchedulesService  $backupScheduleService  The service responsible for handling backup schedule operations
     * @param  CleanupSchedulesService  $cleanupScheduleService  The service responsible for handling cleanup schedule operations
     * @param  SchedulesUiResponder  $ui  The UI responder responsible for rendering responses for schedule operations
     */
    public function __construct(
        protected readonly BackupSchedulesService $backupScheduleService,
        protected readonly CleanupSchedulesService $cleanupScheduleService,
        protected readonly SchedulesUiResponder $ui
    ) {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->ui->renderSchedulesList(new SchedulesListViewData(
            backupSchedules: $this->backupScheduleService->getBackupSchedules(),
            cleanupSchedules: $this->cleanupScheduleService->getCleanupSchedules(),
        ));
    }
}
