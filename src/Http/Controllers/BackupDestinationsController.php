<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use SameOldNick\BackupManager\Contracts\Responders\BackupDestinationsUiResponder;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\BackupDestinationsListViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\DestroyBackupDestinationViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\EditBackupDestinationViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\StoreBackupDestinationViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\UpdateBackupDestinationViewData;
use SameOldNick\BackupManager\DataTransferObjects\Services\CreateBackupDestinationData;
use SameOldNick\BackupManager\DataTransferObjects\Services\UpdateBackupDestinationData;
use SameOldNick\BackupManager\Http\Requests\StoreBackupDestinationRequest;
use SameOldNick\BackupManager\Http\Requests\UpdateBackupDestinationRequest;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Services\BackupDestinationsService;
use Spatie\Backup\Config\Config;

class BackupDestinationsController
{
    /**
     * BackupDestinationsController constructor.
     *
     * @param  BackupDestinationsService  $service  The service responsible for handling backup destination operations
     * @param  BackupDestinationsUiResponder  $ui  The UI responder responsible for rendering responses for backup destination operations
     */
    public function __construct(
        protected readonly BackupDestinationsService $service,
        protected readonly BackupDestinationsUiResponder $ui
    ) {
        //
    }

    /**
     * List all configurations
     *
     * @return void
     */
    public function index(Request $request)
    {
        $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'query' => ['sometimes', 'string'],
            'status' => ['sometimes', Rule::in(['enabled', 'disabled'])],
        ]);

        $isActive = $request->filled('status') ? ($request->str('status') === 'enabled') : null;
        $query = $request->filled('query') ? $request->str('query') : null;

        $destinations = $this->service->getBackupDestinations($isActive, $query);

        return $this->ui->renderBackupDestinationsList(new BackupDestinationsListViewData(
            backupDestinations: $destinations,
        ));
    }

    /**
     * Create a new configuration
     *
     * @return mixed
     */
    public function create()
    {
        return $this->ui->renderCreateBackupDestination();
    }

    /**
     * Store a new configuration
     *
     * @return mixed
     */
    public function store(StoreBackupDestinationRequest $request)
    {
        $destination = $this->service->createBackupDestination(
            CreateBackupDestinationData::fromArray($request->validated())
        );

        return $this->ui->renderStoreBackupDestination(new StoreBackupDestinationViewData(
            configuration: $destination,
        ));
    }

    /**
     * Show a specific configuration
     *
     * @return mixed
     */
    public function show(Config $backupConfig, FilesystemConfiguration $destination)
    {
        $config = $destination->configurable;

        if (! $config) {
            return response()->json(['message' => __('backup::messages.destination_unconfigured')], 404);
        }

        return $this->ui->renderEditBackupDestination(new EditBackupDestinationViewData(
            backupConfig: $backupConfig,
            configuration: $destination,
        ));
    }

    /**
     * Update an existing configuration
     *
     * @return mixed
     */
    public function update(UpdateBackupDestinationRequest $request, FilesystemConfiguration $destination)
    {
        $config = $destination->configurable;

        if (! $config) {
            abort(404, __('backup::messages.destination_not_found'));
        }

        $destination = $this->service->updateBackupDestination(
            $destination,
            UpdateBackupDestinationData::fromArray($request->validated())
        );

        return $this->ui->renderUpdateBackupDestination(new UpdateBackupDestinationViewData(
            destination: $destination,
        ));
    }

    /**
     * Delete a configuration
     *
     * @return mixed
     */
    public function destroy(FilesystemConfiguration $destination)
    {
        $this->service->removeBackupDestination($destination);

        return $this->ui->renderDestroyBackupDestination(new DestroyBackupDestinationViewData(
            destination: $destination,
        ));
    }
}
