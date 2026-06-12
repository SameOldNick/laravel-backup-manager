<?php

use Illuminate\Support\Facades\Route;
use SameOldNick\BackupManager\Enums\BackupTypes;
use SameOldNick\BackupManager\Http\Controllers;

Route::group(config('backup-manager.routes.all', []), function () {
    Route::group(config('backup-manager.routes.management', []), function () {
        Route::group(config('backup-manager.routes.backups', []), function () {
            Route::get('/', [Controllers\BackupController::class, 'index'])->name('index');
            Route::get('/{backup}/download', [Controllers\BackupController::class, 'generateDownloadLink'])->name('download');

        });

        Route::group(config('backup-manager.routes.perform', []), function () {
            Route::post('/', [Controllers\PerformBackupController::class, 'initialize'])
                ->name('initialize');

            Route::post('/start', [Controllers\PerformBackupController::class, 'start'])
                ->name('start')
                ->middleware('signed');

            Route::get('/{type}/{uuid}', [Controllers\PerformBackupController::class, 'show'])
                ->name('show')
                ->middleware('signed')
                ->whereIn('type', BackupTypes::cases())
                ->whereUuid('uuid');
        });

        Route::group(config('backup-manager.routes.destinations', []), function () {
            Route::get('/', [Controllers\BackupDestinationsController::class, 'index'])->name('index');
            Route::get('/create', [Controllers\BackupDestinationsController::class, 'create'])->name('create');
            Route::post('/', [Controllers\BackupDestinationsController::class, 'store'])->name('store');
            Route::get('/{destination}', [Controllers\BackupDestinationsController::class, 'show'])->name('show');
            Route::put('/{destination}', [Controllers\BackupDestinationsController::class, 'update'])->name('update');
            Route::delete('/{destination}', [Controllers\BackupDestinationsController::class, 'destroy'])->name('destroy');

            Route::prefix('/{destination}/test')->name('test.')->group(function () {
                Route::post('/initialize', [Controllers\BackupDestinationTestController::class, 'initialize'])->name('initialize');
                Route::post('/start', [Controllers\BackupDestinationTestController::class, 'start'])
                    ->name('start')
                    ->middleware('signed');
                Route::get('/{uuid}', [Controllers\BackupDestinationTestController::class, 'show'])
                    ->name('show')
                    ->middleware('signed')
                    ->whereUuid('uuid');
            });
        });

        Route::group(config('backup-manager.routes.schedules', []), function () {
            Route::get('/', [Controllers\ScheduleController::class, 'index'])->name('index');

            Route::resource('backup', Controllers\BackupScheduleController::class)->parameters([
                'backup' => 'schedule',
            ])->only(['create', 'store', 'edit', 'update', 'destroy']);

            Route::resource('cleanup', Controllers\CleanupScheduleController::class)->parameters([
                'cleanup' => 'schedule',
            ])->only(['create', 'store', 'edit', 'update', 'destroy']);
        });
    });

    Route::group(config('backup-manager.routes.download', []), function () {
        Route::group(config('backup-manager.routes.files', []), function () {
            Route::get('/{file}', [Controllers\BackupFileController::class, 'retrieve'])->name('file');
        });
    });
});
