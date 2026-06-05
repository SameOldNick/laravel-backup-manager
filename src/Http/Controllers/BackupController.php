<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use SameOldNick\BackupManager\Broadcasting\Access\ChannelAccessManager;
use SameOldNick\BackupManager\Contracts\Responders\BackupsUiResponder;
use SameOldNick\BackupManager\Jobs\Notifiable\BackupJob;
use SameOldNick\BackupManager\Models\Backup;
use SameOldNick\BackupManager\Models\Collections\BackupCollection;
use SameOldNick\BackupManager\Models\File;

class BackupController
{
    use DispatchesJobs;

    public function __construct(
        protected readonly BackupsUiResponder $ui
    ) {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'query' => ['sometimes', 'string'],
            'status' => ['sometimes', Rule::in([
                'all',
                Backup::STATUS_SUCCESSFUL,
                Backup::STATUS_FAILED,
                Backup::STATUS_DELETED,
                Backup::STATUS_FILE_NOT_FOUND,
            ])],
        ]);

        return $this->ui->renderBackupsList($this->filterBackups(
            status: $request->filled('status') ? $request->str('status')->toString() : null,
            query: $request->filled('query') ? $request->str('query')->toString() : null,
        )->paginate($request->query('per_page', 15))->withQueryString());
    }

    /**
     * Generates download link to backup file.
     *
     * @return array
     */
    public function generateDownloadLink(Backup $backup)
    {
        if (! $backup->file) {
            abort(404, __('backup::messages.file_not_found'));
        }

        return redirect()->temporarySignedRoute('backup.file', 5 * 60, ['file' => $backup->file]);
    }

    /**
     * Performs a backup
     */
    public function performBackup(ChannelAccessManager $channelAccessManager, Request $request)
    {
        $request->validate([
            'type' => 'required|in:full,database,files',
        ]);

        /**
         * The backup is run using a job.
         * This allows it to run asynchronously (so websocket can handle it).
         */
        $type = $request->str('type');

        $uuid = Str::uuid();
        $channel = $channelAccessManager->createChannelId('backups', $uuid);

        $lease = $channelAccessManager->open(
            channelId: $channel,
            notifiable: $request->user(),
            expiresAt: now()->addHours(3),
        );

        $backupType = match (true) {
            $type->exactly('database') => BackupJob::BACKUP_ONLY_DATABASES,
            $type->exactly('files') => BackupJob::BACKUP_ONLY_FILES,
            default => BackupJob::BACKUP_FULL,
        };

        $this->dispatch(new BackupJob($channel, $request->user(), $backupType));

        return redirect()->temporarySignedRoute('backup.backups.perform.show', $lease->expiresAt, [
            'type' => $type,
            'uuid' => $uuid,
        ]);
    }

    /**
     * Shows the perform backup page.
     */
    public function showPerform(string $type, string $uuid)
    {
        return $this->ui->renderPerformBackup($type, $uuid);
    }

    /**
     * Filters the backups based on the given status and query.
     *
     * @param  string|null  $status  The status to filter by (successful, failed, deleted)
     * @param  string|null  $query  The search query to filter by (matches uuid, status, or file path)
     * @return BackupCollection
     */
    protected function filterBackups(?string $status = null, ?string $query = null)
    {
        /**
         * Since the where() method creates WHERE clauses for actual columns, we can't use it for appended attributes.
         * Instead, we can use afterQuery() to filter the models after their pulled.
         */
        return Backup::query()->withTrashed()->afterQuery(function (BackupCollection $found) use ($status, $query) {
            $collection = ! is_null($status) && $status !== 'all' ? $found->status($status) : $found;

            if (! is_null($query)) {
                $collection = $collection->filter(function (Backup $backup) use ($query) {
                    return str_contains($backup->uuid, $query)
                        || str_contains($backup->status, $query)
                        || str_contains($backup->file?->path ?? '', $query);
                });
            }

            /**
             * The keys need to be reset so they are in sequence (0,1,2...)
             * Passing the keys without being in sequence causes issues with pagination.
             * It also causes JS to treat the data as an object, not an array.
             */
            return $collection->values();
        })->latest();
    }
}
