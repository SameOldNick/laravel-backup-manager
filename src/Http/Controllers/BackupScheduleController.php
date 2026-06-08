<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use SameOldNick\BackupManager\Contracts\Responders\BackupSchedulesUiResponder;
use SameOldNick\BackupManager\DataTransferObjects\CreateBackupScheduleData;
use SameOldNick\BackupManager\DataTransferObjects\UpdateBackupScheduleData;
use SameOldNick\BackupManager\Http\Requests\StoreBackupScheduleRequest;
use SameOldNick\BackupManager\Http\Requests\UpdateBackupScheduleRequest;
use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Services\BackupSchedulesService;

class BackupScheduleController
{
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
        return $this->ui->renderCreateBackupSchedule(
            FilesystemConfiguration::query()
                ->active()
                ->orderBy('name')
                ->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBackupScheduleRequest $request)
    {
        $data = CreateBackupScheduleData::fromArray($request->validated());

        $schedule = $this->service->createBackupSchedule($data);

        return $this->ui->renderStoreBackupSchedule($schedule);
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

        $destinations = FilesystemConfiguration::query()
            ->where(function ($query) use ($selectedDestinationIds) {
                $query->active();

                if (count($selectedDestinationIds) > 0) {
                    $query->orWhereIn('id', $selectedDestinationIds);
                }
            })
            ->orderBy('name')
            ->get();

        return $this->ui->renderEditBackupSchedule($schedule, $destinations);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBackupScheduleRequest $request, BackupSchedule $schedule)
    {
        $data = UpdateBackupScheduleData::fromArray($request->validated());

        $schedule = $this->service->updateBackupSchedule($schedule, $data);

        return $this->ui->renderUpdateBackupSchedule($schedule);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BackupSchedule $schedule)
    {
        $this->service->removeBackupSchedule($schedule);

        return $this->ui->renderDestroyBackupSchedule($schedule);
    }
}
