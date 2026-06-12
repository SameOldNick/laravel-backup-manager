<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use SameOldNick\BackupManager\Contracts\Responders\BackupSchedulesUiResponder;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\CreateBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\DestroyBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\EditBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\StoreBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Schedules\BackupSchedules\UpdateBackupScheduleViewData;
use SameOldNick\BackupManager\DataTransferObjects\Services\CreateBackupScheduleData;
use SameOldNick\BackupManager\DataTransferObjects\Services\UpdateBackupScheduleData;
use SameOldNick\BackupManager\Http\Requests\StoreBackupScheduleRequest;
use SameOldNick\BackupManager\Http\Requests\UpdateBackupScheduleRequest;
use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Services\BackupSchedulesService;

class BackupScheduleController
{
    /**
     * BackupScheduleController constructor.
     *
     * @param  BackupSchedulesService  $service  The service responsible for handling backup schedule operations
     * @param  BackupSchedulesUiResponder  $ui  The UI responder responsible for rendering responses for backup schedule operations
     */
    public function __construct(
        protected readonly BackupSchedulesService $service,
        protected readonly BackupSchedulesUiResponder $ui
    ) {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return $this->ui->renderCreateBackupSchedule(new CreateBackupScheduleViewData(
            configurations: $this->service->getAvailableDestinations(),
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBackupScheduleRequest $request)
    {
        $data = CreateBackupScheduleData::fromArray($request->validated());

        $schedule = $this->service->createBackupSchedule($data);

        return $this->ui->renderStoreBackupSchedule(new StoreBackupScheduleViewData(
            schedule: $schedule,
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BackupSchedule $schedule)
    {
        $selectedDestinationIds = $schedule
            ->filesystemConfigurations()
            ->pluck('filesystem_configurations.id')
            ->all();

        $destinations = $this->service->getAvailableDestinations()->filter(function ($destination) use ($selectedDestinationIds) {
            return $destination->is_active || in_array($destination->id, $selectedDestinationIds);
        });

        return $this->ui->renderEditBackupSchedule(new EditBackupScheduleViewData(
            schedule: $schedule,
            configurations: $destinations,
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBackupScheduleRequest $request, BackupSchedule $schedule)
    {
        $data = UpdateBackupScheduleData::fromArray($request->validated());

        $schedule = $this->service->updateBackupSchedule($schedule, $data);

        return $this->ui->renderUpdateBackupSchedule(new UpdateBackupScheduleViewData(
            schedule: $schedule,
        ));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BackupSchedule $schedule)
    {
        $this->service->removeBackupSchedule($schedule);

        return $this->ui->renderDestroyBackupSchedule(new DestroyBackupScheduleViewData(
            schedule: $schedule,
        ));
    }
}
