<?php

use Illuminate\Support\Facades\Route;
use Modules\CashRegisters\app\Http\Controllers\ZReportController;

Route::middleware(['auth'])->group(function () {

    Route::middleware('permission:view-cash-registers')->group(function () {
        Route::get('/cash-registers', fn () => view('cashregisters::registers'))->name('cash-registers.index');
    });

    Route::middleware('permission:view-cash-sessions')->group(function () {
        Route::get('/cash-sessions',             fn () => view('cashregisters::sessions'))->name('cash-sessions.index');
        Route::get('/cash-sessions/{id}/report', [ZReportController::class, 'show'])->name('cash-sessions.report');
        Route::get('/cash-sessions/{id}/pdf',    [ZReportController::class, 'pdf'])
            ->middleware('throttle:exports')
            ->name('cash-sessions.pdf');
    });
});
