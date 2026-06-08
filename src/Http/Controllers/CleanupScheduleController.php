<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use SameOldNick\BackupManager\Contracts\Responders\CleanupSchedulesUiResponder;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\DestroyCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\EditCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\StoreCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\CleanupSchedules\UpdateCleanupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Services\CreateCleanupScheduleData;
use SameOldNick\BackupManager\DataTransferObjects\Services\UpdateCleanupScheduleData;
use SameOldNick\BackupManager\Http\Requests\StoreCleanupScheduleRequest;
use SameOldNick\BackupManager\Http\Requests\UpdateCleanupScheduleRequest;
use SameOldNick\BackupManager\Models\CleanupSchedule;
use SameOldNick\BackupManager\Services\CleanupSchedulesService;

class CleanupScheduleController
{
    public function __construct(
        protected readonly CleanupSchedulesService $service,
        protected readonly CleanupSchedulesUiResponder $ui
    ) {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return $this->ui->renderCreateCleanupSchedule();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCleanupScheduleRequest $request)
    {
        $data = CreateCleanupScheduleData::fromArray($request->validated());

        $schedule = $this->service->createCleanupSchedule($data);

        return $this->ui->renderStoreCleanupSchedule(new StoreCleanupScheduleViewData(
            schedule: $schedule,
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CleanupSchedule $schedule)
    {
        return $this->ui->renderEditCleanupSchedule(new EditCleanupScheduleViewData(
            schedule: $schedule,
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCleanupScheduleRequest $request, CleanupSchedule $schedule)
    {
        $data = UpdateCleanupScheduleData::fromArray($request->validated());

        $this->service->updateCleanupSchedule($schedule, $data);

        return $this->ui->renderUpdateCleanupSchedule(new UpdateCleanupScheduleViewData(
            schedule: $schedule,
        ));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CleanupSchedule $schedule)
    {
        $this->service->removeCleanupSchedule($schedule);

        return $this->ui->renderDestroyCleanupSchedule(new DestroyCleanupScheduleViewData(
            schedule: $schedule,
        ));
    }
}
