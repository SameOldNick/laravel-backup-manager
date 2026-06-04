<?php

use Illuminate\Support\Facades\Route;
use SameOldNick\BackupManager\Http\Controllers;

Route::group(config('backup-manager.routes.all', []), function () {
    Route::group(config('backup-manager.routes.download', []), function () {
        Route::get('/files/{file}', [Controllers\FileController::class, 'retrieve'])->name('file');
    });
});
