<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use SameOldNick\BackupManager\Contracts\Responders\BackupDestinationsUiResponder;
use SameOldNick\BackupManager\DataTransferObjects\CreateBackupDestinationData;
use SameOldNick\BackupManager\DataTransferObjects\UpdateBackupDestinationData;
use SameOldNick\BackupManager\Http\Requests\StoreBackupDestinationRequest;
use SameOldNick\BackupManager\Http\Requests\UpdateBackupDestinationRequest;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Services\BackupDestinationsService;
use Spatie\Backup\Config\Config;

class BackupDestinationsController
{
    use DispatchesJobs;

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

        return $this->ui->renderBackupDestinationsList($destinations);
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

        return $this->ui->renderStoreBackupDestination($destination);
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

        return $this->createEditResponse($backupConfig, $destination);
    }

    /**
     * Test the destination works.
     *
     * @return mixed
     */
    public function test(FilesystemConfiguration $destination)
    {
        $uuid = Str::uuid();

        $lease = $this->service->startBackupDestinationTest($destination, $uuid);

        return redirect()->temporarySignedRoute('backup.destinations.test.result', $lease->expiresAt, [
            'destination' => $destination->id,
            'uuid' => $uuid,
        ]);
    }

    /**
     * Show the result of a test.
     *
     * @return mixed
     */
    public function showTestResult(Config $backupConfig, FilesystemConfiguration $destination, string $uuid)
    {
        return
            $this->createEditResponse($backupConfig, $destination)
                ->with('testUuid', $uuid);
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

        return $this->ui->renderUpdateBackupDestination($destination);
    }

    /**
     * Delete a configuration
     *
     * @return mixed
     */
    public function destroy(FilesystemConfiguration $destination)
    {
        $this->performDestroy($destination);

        return $this->ui->renderDestroyBackupDestination($destination);
    }

    /**
     * Show the edit page for a specific configuration
     *
     * @return mixed
     */
    protected function createEditResponse(Config $backupConfig, FilesystemConfiguration $destination)
    {
        // Pull disks indirectly through Spatie Backup config
        $enabled = $backupConfig->backup->destination->disks;

        return $this->ui->renderEditBackupDestination(
            $backupConfig,
            $destination,
            in_array($destination->driver_name, $enabled, true)
        );
    }

    /**
     * Removes a configuration
     *
     * @return void
     */
    protected function performDestroy(FilesystemConfiguration $destination)
    {
        $destination->configurable->delete();
        $destination->delete();
    }
}
