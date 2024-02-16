<?php

use Illuminate\Support\Facades\Route;
use Tv2regionerne\StatamicEvents\Http\Controllers\ExecutionController;
use Tv2regionerne\StatamicEvents\Http\Controllers\HandlerActionsController;
use Tv2regionerne\StatamicEvents\Http\Controllers\HandlerController;
use Tv2regionerne\StatamicEvents\Http\Controllers\TriggerController;

Route::name('statamic-events.')->prefix('statamic-events')->group(function () {
    Route::name('handlers.')->prefix('handlers')->group(function () {
        Route::get('/', [HandlerController::class, 'index'])->name('index');

        Route::get('/listing-api', [HandlerController::class, 'api'])->name('listing-api');
        Route::post('/actions', [HandlerActionsController::class, 'runAction'])->name('actions.run');
        Route::post('/actions/list', [HandlerActionsController::class, 'bulkActionsList'])->name('actions.bulk');

        Route::get('/create', [HandlerController::class, 'create'])->name('create');
        Route::post('/create', [HandlerController::class, 'store'])->name('store');
        Route::get('/{record}', [HandlerController::class, 'edit'])->name('edit');
        Route::patch('/{record}', [HandlerController::class, 'update'])->name('update');
    });

    Route::name('executions.')->prefix('executions')->group(function () {
        Route::get('/', [ExecutionController::class, 'index'])->name('index');
        Route::get('/listing-api', [ExecutionController::class, 'api'])->name('listing-api');
        Route::post('/actions', [HandlerActionsController::class, 'runAction'])->name('actions.run');
        Route::post('/actions/list', [HandlerActionsController::class, 'bulkActionsList'])->name('actions.bulk');

        Route::get('/{record}', [ExecutionController::class, 'show'])->name('show');
    });

    Route::post('/trigger', [TriggerController::class, 'index'])->name('trigger');
});
