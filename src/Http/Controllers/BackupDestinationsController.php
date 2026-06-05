<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use SameOldNick\BackupManager\Broadcasting\Access\ChannelAccessManager;
use SameOldNick\BackupManager\Contracts\Responders\BackupDestinationsUiResponder;
use SameOldNick\BackupManager\Jobs\Notifiable\FilesystemConfigurationTestJob;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Models\FilesystemConfigurationFTP;
use SameOldNick\BackupManager\Models\FilesystemConfigurationLocal;
use SameOldNick\BackupManager\Models\FilesystemConfigurationSFTP;
use SameOldNick\BackupManager\Rules\RelativePath;
use SameOldNick\BackupManager\Rules\Slugified;
use Spatie\Backup\Config\Config;

class BackupDestinationsController
{
    use DispatchesJobs;

    public function __construct(
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

        $query = FilesystemConfiguration::query()->afterQuery(function (Collection $found) use ($request) {
            $isActive = $request->filled('status') ? ($request->str('status') === 'enabled') : null;
            $query = $request->str('query');

            $collection = $found->filter(function (FilesystemConfiguration $config) use ($isActive, $query) {
                if ($isActive !== null && $config->is_active !== $isActive) {
                    return false;
                }

                if ($query &&
                    ! str_contains($config->name, $query) &&
                    ! str_contains($config->type, $query) &&
                    ! str_contains($config->host, $query)) {
                    return false;
                }

                return true;
            });

            /**
             * The keys need to be reset so they are in sequence (0,1,2...)
             * Passing the keys without being in sequence causes issues with pagination.
             * It also causes JS to treat the data as an object, not an array.
             */
            return ! is_null($collection) ? $collection->values() : null;
        })->latest();

        return $this->ui->renderBackupDestinationsList($query->paginate($request->integer('per_page', 15))->withQueryString());
    }

    /**
     * Create a new configuration
     *
     * @return mixed
     */
    public function create(Request $request)
    {
        return $this->ui->renderCreateBackupDestination($request);
    }

    /**
     * Store a new configuration
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(FilesystemConfiguration::class),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique(FilesystemConfiguration::class),
                new Slugified,
            ],
            'type' => 'required|in:local,ftp,sftp',
            'host' => 'nullable|required_if:type,ftp,sftp|string|max:255',
            'port' => 'nullable|required_if:type,ftp,sftp|integer|min:1|max:65535',
            'auth_type' => 'nullable|required_if:type,sftp|string|in:password,key',
            'username' => [
                'nullable',
                Rule::requiredIf(fn () => $request->type === 'ftp' || $request->type === 'sftp'),
                'string',
                'max:255',
            ],
            'password' => [
                'nullable',
                Rule::requiredIf(fn () => $request->type === 'ftp' || ($request->type === 'sftp' && $request->auth_type === 'password')),
                'string',
                'max:255',
            ],
            'root' => [
                'nullable',
                Rule::requiredIf(fn () => $request->type === 'local'),
                'string',
                'max:255',
                ...($request->type === 'local' ? [new RelativePath] : []),
            ],
            'private_key' => [
                'nullable',
                Rule::requiredIf(fn () => $request->type === 'sftp' && $request->auth_type === 'key'),
                'string',
            ],
            'passphrase' => 'nullable|string|max:255',
            'extra' => 'nullable|array',
        ]);

        $config = match ($request->type) {
            'local' => FilesystemConfigurationLocal::create($request->only([
                'root',
                'extra',
            ])),
            'ftp' => FilesystemConfigurationFTP::create($request->only([
                'host',
                'port',
                'username',
                'password',
                'root',
                'extra',
            ])),
            'sftp' => FilesystemConfigurationSFTP::create($request->only([
                'host',
                'port',
                'username',
                'password',
                'private_key',
                'passphrase',
                'root',
                'extra',
            ])),
            default => null
        };

        // The validator shouldn't allow this, but just in case.
        if (! $config) {
            return response()->json(['message' => 'Type is invalid.'], 500);
        }

        $fsConfig = new FilesystemConfiguration([
            'name' => $request->name,
            'slug' => $request->filled('slug') ? $request->slug : Str::slug($request->name),
            'disk_type' => $request->type,
            'is_active' => $request->boolean('enabled'),
        ]);

        $fsConfig->configurable()->associate($config);

        $fsConfig->save();

        return $this->ui->renderStoreBackupDestination($fsConfig);
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
    public function test(ChannelAccessManager $channelAccessManager, Request $request, FilesystemConfiguration $destination)
    {
        $uuid = Str::uuid();
        $channel = $channelAccessManager->createChannelId('test-destination', $uuid);

        $lease = $channelAccessManager->open(
            channelId: $channel,
            notifiable: $request->user(),
            expiresAt: now()->addHours(3),
        );

        $this->dispatch(new FilesystemConfigurationTestJob($channel, $request->user(), $destination->driver_name));

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
