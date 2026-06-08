<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use SameOldNick\BackupManager\Contracts\Responders\BackupDestinationsUiResponder;
use SameOldNick\BackupManager\DataTransferObjects\CreateBackupDestinationData;
use SameOldNick\BackupManager\Http\Requests\StoreBackupDestinationRequest;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Rules\RelativePath;
use SameOldNick\BackupManager\Rules\Slugified;
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
    public function update(Request $request, FilesystemConfiguration $destination)
    {
        $validated = $request->validate([
            'enabled' => ['sometimes', 'boolean'],
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique(FilesystemConfiguration::class)->ignore($destination),
            ],
            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::unique(FilesystemConfiguration::class)->ignore($destination),
                new Slugified,
            ],
            'host' => ['sometimes', 'string', 'max:255'],
            'port' => ['sometimes', 'integer', 'min:1', 'max:65535'],
            'username' => ['sometimes', 'string', 'max:255'],
            'auth_type' => ['sometimes', 'nullable', 'string', 'in:password,key'],
            'password' => [
                'sometimes',
                'string',
                'max:255',
                'required_with:confirm_password',
            ],
            'confirm_password' => [
                'sometimes',
                'string',
                'required_with:password',
                'same:password',
            ],
            'private_key' => ['sometimes', 'string'],
            'passphrase' => ['sometimes', 'nullable', 'string', 'max:255'],
            'root' => [
                'sometimes',
                'string',
                'max:255',
                ...($destination->disk_type === 'local' ? [new RelativePath] : []),
            ],
            'extra' => ['sometimes', 'nullable', 'array'],
        ]);

        $config = $destination->configurable;

        if (! $config) {
            return response()->json(['message' => __('backup::messages.destination_not_found')], 404);
        }

        $input = Arr::except($validated, ['enabled']);

        // Only include enabled if it exists
        if (Arr::has($validated, 'enabled')) {
            // Convert it to a boolean
            $input['enabled'] = (bool) Arr::get($validated, 'enabled');
        }

        $this->performUpdate($destination, $input);

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
     * Updates a configuration
     *
     * @return FilesystemConfiguration
     */
    protected function performUpdate(FilesystemConfiguration $destination, array $input)
    {
        $data = Arr::only($input, [
            'enabled',
            'host',
            'port',
            'username',
            'passphrase',
            'extra',
        ]);

        if (Arr::has($input, 'enabled')) {
            $destination->is_active = (bool) Arr::get($input, 'enabled');
        }

        if (Arr::has($input, 'name')) {
            $destination->name = Arr::get($input, 'name');
        }

        if (Arr::has($input, 'slug')) {
            $destination->slug = Arr::get($input, 'slug');
        }

        if ($destination->isDirty()) {
            $destination->save();
        }

        $diskType = $destination->disk_type;
        $authType = Arr::get($input, 'auth_type');

        if ($diskType === 'local' && Arr::has($input, 'root')) {
            validator(
                ['root' => Arr::get($input, 'root')],
                ['root' => ['nullable', 'string', 'max:255', new RelativePath]],
            )->validate();
        }

        if (
            Arr::has($input, 'password') && (
                $authType &&
                ($diskType === 'ftp' || ($diskType === 'sftp' && $authType === 'password'))
            )
        ) {
            $data['private_key'] = null;
            $data['passphrase'] = null;
            $data['password'] = Arr::get($input, 'password');
        } elseif (
            Arr::has($input, 'private_key') && (
                $authType && $diskType === 'sftp' && $authType === 'key'
            )
        ) {
            $data['password'] = null;
            $data['private_key'] = Arr::get($input, 'private_key');
            $data['passphrase'] = Arr::get($input, 'passphrase');
        }

        if (in_array($diskType, ['local', 'ftp'], true) && Arr::has($input, 'root')) {
            $data['root'] = Arr::get($input, 'root');
        }

        $destination->configurable->update($data);

        return $destination;
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
