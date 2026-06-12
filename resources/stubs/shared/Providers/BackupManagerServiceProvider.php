<?php

namespace VendorName\Providers;

use Illuminate\Support\ServiceProvider;
use SameOldNick\BackupManager\Contracts\Responders as UiResponderContracts;
use VendorName\BackupManager\Responders;

class BackupManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UiResponderContracts\BackupDestinationsUiResponder::class, Responders\BackupDestinationsUiResponder::class);
        $this->app->bind(UiResponderContracts\BackupDestinationTestUiResponder::class, Responders\BackupDestinationTestUiResponder::class);
        $this->app->bind(UiResponderContracts\BackupSchedulesUiResponder::class, Responders\BackupSchedulesUiResponder::class);
        $this->app->bind(UiResponderContracts\BackupsUiResponder::class, Responders\BackupsUiResponder::class);
        $this->app->bind(UiResponderContracts\PerformBackupUiResponder::class, Responders\PerformBackupUiResponder::class);
        $this->app->bind(UiResponderContracts\CleanupSchedulesUiResponder::class, Responders\CleanupSchedulesUiResponder::class);
        $this->app->bind(UiResponderContracts\SchedulesUiResponder::class, Responders\SchedulesUiResponder::class);
    }
}
