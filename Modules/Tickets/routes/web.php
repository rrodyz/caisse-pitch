<?php

use Illuminate\Support\Facades\Route;
use Modules\Tickets\app\Http\Controllers\TicketController;

Route::middleware('auth')->group(function () {
    Route::get('/tickets/{id}',      [TicketController::class, 'show'])->name('tickets.show');
    Route::get('/tickets/{id}/pdf',  [TicketController::class, 'pdf'])->name('tickets.pdf');
});
