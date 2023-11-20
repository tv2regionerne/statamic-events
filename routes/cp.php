<?php

use Tv2regionerne\StatamicEvents\Http\Controllers\CpActionsController;
use Tv2regionerne\StatamicEvents\Http\Controllers\CpController;
use Illuminate\Support\Facades\Route;

Route::name('statamic-events.')->prefix('statamic-events')->group(function () {
    Route::get('/', [CpController::class, 'index'])->name('index');

    Route::get('/listing-api', [CpController::class, 'api'])->name('listing-api');
    Route::post('/actions', [CpActionsController::class, 'runAction'])->name('actions.run');
    Route::post('/actions/list', [CpActionsController::class, 'bulkActionsList'])->name('actions.bulk');

    Route::get('/create', [CpController::class, 'create'])->name('create');
    Route::post('/create', [CpController::class, 'store'])->name('store');
    Route::get('/{record}', [CpController::class, 'edit'])->name('edit');
    Route::patch('/{record}', [CpController::class, 'update'])->name('update');
});
