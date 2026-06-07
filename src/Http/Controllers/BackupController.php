<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use SameOldNick\BackupManager\Contracts\Responders\BackupsUiResponder;
use SameOldNick\BackupManager\Jobs\Notifiable\BackupJob;
use SameOldNick\BackupManager\Models\Backup;
use SameOldNick\BackupManager\Services\BackupsService;

class BackupController
{
    use DispatchesJobs;

    public function __construct(
        protected readonly BackupsService $service,
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

        $backups = $this->service->getBackups(
            status: $request->filled('status') ? $request->str('status')->toString() : null,
            query: $request->filled('query') ? $request->str('query')->toString() : null,
        );

        return $this->ui->renderBackupsList($backups);
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

        $link = $this->service->createBackupDownloadLink($backup);

        return redirect($link);
    }

    /**
     * Performs a backup
     */
    public function performBackup(Request $request)
    {
        $request->validate([
            'type' => 'required|in:full,database,files',
        ]);

        /**
         * The backup is run using a job.
         * This allows it to run asynchronously (so websocket can handle it).
         */
        $uuid = Str::uuid();
        $type = $request->str('type')->toString();

        $backupType = match ($type) {
            'database' => BackupJob::BACKUP_ONLY_DATABASES,
            'files' => BackupJob::BACKUP_ONLY_FILES,
            default => BackupJob::BACKUP_FULL,
        };

        $lease = $this->service->startBackup(
            type: $backupType,
            user: $request->user(),
            uuid: $uuid,
        );

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
        $lease = $this->service->getBackupChannelLease($this->service->createChannelId($uuid));

        return $this->ui->renderPerformBackup($type, $uuid, $lease);
    }
}
