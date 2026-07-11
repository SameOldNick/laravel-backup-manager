<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use Illuminate\Http\Request;
use SameOldNick\BackupManager\Contracts\Responders\BackupMonitorsUiResponder;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\BackupMonitorsListViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\DestroyBackupMonitorViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\EditBackupMonitorViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\StoreBackupMonitorViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupMonitors\UpdateBackupMonitorViewData;
use SameOldNick\BackupManager\DataTransferObjects\Services\CreateBackupMonitorData;
use SameOldNick\BackupManager\DataTransferObjects\Services\UpdateBackupMonitorData;
use SameOldNick\BackupManager\Http\Requests\StoreBackupMonitorRequest;
use SameOldNick\BackupManager\Http\Requests\UpdateBackupMonitorRequest;
use SameOldNick\BackupManager\Models\BackupMonitor;
use SameOldNick\BackupManager\Services\BackupMonitorsService;

class BackupMonitorController
{
    public function __construct(
        protected readonly BackupMonitorsService $service,
        protected readonly BackupMonitorsUiResponder $responder,
    ) {
    }

    public function index(Request $request)
    {
        $monitors = $this->service->getBackupMonitors(
            active: $request->boolean('active', null),
            query: $request->string('query')->toString() ?: null,
        );

        return $this->responder->renderBackupMonitorsList(
            new BackupMonitorsListViewData($monitors)
        );
    }

    public function create()
    {
        return $this->responder->renderCreateBackupMonitor();
    }

    public function store(StoreBackupMonitorRequest $request)
    {
        $monitor = $this->service->createBackupMonitor(
            new CreateBackupMonitorData(
                name: $request->validated('name'),
                disks: $request->validated('disks'),
                maximumAgeInDays: $request->validated('maximum_age_in_days'),
                maximumStorageInMegabytes: $request->validated('maximum_storage_in_megabytes'),
                enabled: $request->boolean('enabled', true),
            )
        );

        return $this->responder->renderStoreBackupMonitor(
            new StoreBackupMonitorViewData($monitor)
        );
    }

    public function edit(BackupMonitor $monitor)
    {
        return $this->responder->renderEditBackupMonitor(
            new EditBackupMonitorViewData($monitor)
        );
    }

    public function update(UpdateBackupMonitorRequest $request, BackupMonitor $monitor)
    {
        $monitor = $this->service->updateBackupMonitor(
            $monitor,
            new UpdateBackupMonitorData(
                name: $request->validated('name'),
                disks: $request->validated('disks'),
                maximumAgeInDays: $request->validated('maximum_age_in_days'),
                maximumStorageInMegabytes: $request->validated('maximum_storage_in_megabytes'),
                enabled: $request->has('enabled') ? $request->boolean('enabled') : null,
            )
        );

        return $this->responder->renderUpdateBackupMonitor(
            new UpdateBackupMonitorViewData($monitor)
        );
    }

    public function destroy(BackupMonitor $monitor)
    {
        $id = $monitor->id;

        $this->service->removeBackupMonitor($monitor);

        return $this->responder->renderDestroyBackupMonitor(
            new DestroyBackupMonitorViewData($id)
        );
    }
}