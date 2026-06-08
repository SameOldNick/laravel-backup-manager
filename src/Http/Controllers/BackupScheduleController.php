<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use SameOldNick\BackupManager\Contracts\Responders\BackupSchedulesUiResponder;
use SameOldNick\BackupManager\Http\Requests\StoreBackupScheduleRequest;
use SameOldNick\BackupManager\Http\Requests\UpdateBackupScheduleRequest;
use SameOldNick\BackupManager\Models\BackupSchedule;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;

class BackupScheduleController
{
    public function __construct(
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
        $validated = $request->validated();

        $schedule = BackupSchedule::create(collect($validated)->except('destination_ids')->all());

        if (array_key_exists('destination_ids', $validated)) {
            $schedule->filesystemConfigurations()->sync($validated['destination_ids']);
        }

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
        $validated = $request->validated();

        $schedule->update(collect($validated)->except('destination_ids')->all());

        if (array_key_exists('destination_ids', $validated)) {
            $schedule->filesystemConfigurations()->sync($validated['destination_ids']);
        }

        return $this->ui->renderUpdateBackupSchedule($schedule);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BackupSchedule $schedule)
    {
        $schedule->delete();

        return $this->ui->renderDestroyBackupSchedule($schedule);
    }
}
