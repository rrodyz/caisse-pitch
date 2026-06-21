<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:view-stock'])->group(function () {
    Route::get('/stock', fn () => view('stock::index'))->name('stock.index');
});
