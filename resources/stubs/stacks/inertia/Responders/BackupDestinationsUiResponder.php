<?php

namespace VendorName\BackupManager\Responders;

use Inertia\Inertia;
use SameOldNick\BackupManager\Contracts\Responders\BackupDestinationsUiResponder as BackupDestinationsUiResponderContract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\BackupDestinationsListViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\DestroyBackupDestinationViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\EditBackupDestinationViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\StoreBackupDestinationViewData;
use SameOldNick\BackupManager\DataTransferObjects\Responders\BackupDestinations\UpdateBackupDestinationViewData;

class BackupDestinationsUiResponder implements BackupDestinationsUiResponderContract
{
    /**
     * {@inheritDoc}
     */
    public function renderBackupDestinationsList(BackupDestinationsListViewData $data)
    {
        return Inertia::render('dashboard/settings/backups/page', [
            'tab' => 'destinations',
            'action' => 'list',
            'destinations' => $data->backupDestinations,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderCreateBackupDestination()
    {
        return inertia('dashboard/settings/backups/page', [
            'tab' => 'destinations',
            'action' => 'create',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderStoreBackupDestination(StoreBackupDestinationViewData $data)
    {
        return redirect()
            ->route('backup.destinations.show', ['destination' => $data->configuration])
            ->with('success', __('backup::messages.destination_created'));
    }

    /**
     * {@inheritDoc}
     */
    public function renderEditBackupDestination(EditBackupDestinationViewData $data)
    {
        return inertia('dashboard/settings/backups/page', [
            'tab' => 'destinations',
            'action' => 'edit',
            'destination' => $data->configuration,
            'enabled' => $data->configuration->isEnabled($data->backupConfig),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdateBackupDestination(UpdateBackupDestinationViewData $data)
    {
        return back()
            ->with('success', __('backup::messages.destination_updated'));
    }

    /**
     * {@inheritDoc}
     */
    public function renderDestroyBackupDestination(DestroyBackupDestinationViewData $data)
    {
        return back()
            ->with('success', __('backup::messages.destination_deleted'));
    }
}
