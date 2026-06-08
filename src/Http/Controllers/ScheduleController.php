<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use SameOldNick\BackupManager\Contracts\Responders\SchedulesUiResponder;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\SchedulesListViewData;
use SameOldNick\BackupManager\Services\BackupSchedulesService;
use SameOldNick\BackupManager\Services\CleanupSchedulesService;

class ScheduleController
{
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
