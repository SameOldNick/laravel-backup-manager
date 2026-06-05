<?php

use Illuminate\Support\Facades\Route;
use SameOldNick\BackupManager\Http\Controllers;

Route::group(config('backup-manager.routes.all', []), function () {
    Route::group(config('backup-manager.routes.management', []), function () {
        Route::prefix('/backups')->name('backups.')->group(function () {
            Route::get('/', [Controllers\BackupController::class, 'index'])->name('index');
            Route::get('/{backup}/download', [Controllers\BackupController::class, 'generateDownloadLink'])->name('download');
            Route::post('/perform', [Controllers\BackupController::class, 'performBackup'])->name('perform');
            Route::get('/perform/{type}/{uuid}', [Controllers\BackupController::class, 'showPerform'])
                ->name('perform.show')
                ->middleware('signed')
                ->whereIn('type', ['full', 'database', 'files'])
                ->whereUuid('uuid');
        });

        Route::prefix('/destinations')->name('destinations.')->group(function () {
            Route::get('/', [Controllers\BackupDestinationsController::class, 'index'])->name('index');
            Route::get('/create', [Controllers\BackupDestinationsController::class, 'create'])->name('create');
            Route::post('/', [Controllers\BackupDestinationsController::class, 'store'])->name('store');
            Route::get('/{destination}', [Controllers\BackupDestinationsController::class, 'show'])->name('show');
            Route::put('/{destination}', [Controllers\BackupDestinationsController::class, 'update'])->name('update');
            Route::post('/{destination}/test', [Controllers\BackupDestinationsController::class, 'test'])->name('test');
            Route::get('/{destination}/test/{uuid}', [Controllers\BackupDestinationsController::class, 'showTestResult'])
                ->name('test.result')
                ->middleware('signed')
                ->whereUuid('uuid');
            Route::delete('/{destination}', [Controllers\BackupDestinationsController::class, 'destroy'])->name('destroy');
        });

        Route::resource('schedules', Controllers\ScheduleController::class)->only(['index']);

        Route::resource('schedules/backup', Controllers\BackupScheduleController::class)->parameters([
            'backup' => 'schedule',
        ])->names([
            'create' => 'schedules.backup.create',
            'store' => 'schedules.backup.store',
            'edit' => 'schedules.backup.edit',
            'update' => 'schedules.backup.update',
            'destroy' => 'schedules.backup.destroy',
        ])->only(['create', 'store', 'edit', 'update', 'destroy']);

        Route::resource('schedules/cleanup', Controllers\CleanupScheduleController::class)->parameters([
            'cleanup' => 'schedule',
        ])->names([
            'create' => 'schedules.cleanup.create',
            'store' => 'schedules.cleanup.store',
            'edit' => 'schedules.cleanup.edit',
            'update' => 'schedules.cleanup.update',
            'destroy' => 'schedules.cleanup.destroy',
        ])->only(['create', 'store', 'edit', 'update', 'destroy']);
    });

    Route::group(config('backup-manager.routes.download', []), function () {
        Route::get('/files/{file}', [Controllers\FileController::class, 'retrieve'])->name('file');
    });
});
