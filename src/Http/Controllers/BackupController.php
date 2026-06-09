<?php

namespace SameOldNick\BackupManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use SameOldNick\BackupManager\Contracts\Responders\BackupsUiResponder;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Backups\BackupsListViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Backups\PerformBackupViewData;
use SameOldNick\BackupManager\Enums\BackupStatus;
use SameOldNick\BackupManager\Jobs\Notifiable\BackupJob;
use SameOldNick\BackupManager\Models\Backup;
use SameOldNick\BackupManager\Services\BackupsService;

class BackupController
{
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
                ...BackupStatus::cases(),
            ])],
        ]);

        $status = $request->filled('status') && (string) $request->str('status') !== 'all' ? $request->enum('status', BackupStatus::class) : null;
        $query = $request->filled('query') ? $request->str('query')->toString() : null;

        $backups = $this->service->getBackups(
            status: $status,
            query: $query,
        );

        return $this->ui->renderBackupsList(new BackupsListViewData(
            backups: $backups,
        ));
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

        return $this->ui->renderPerformBackup(new PerformBackupViewData(
            type: $type,
            uuid: $uuid,
            lease: $lease,
        ));
    }
}
