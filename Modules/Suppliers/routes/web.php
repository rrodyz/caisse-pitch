<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:view-suppliers'])->group(function () {
    Route::get('/suppliers', fn () => view('suppliers::index'))->name('suppliers.index');
});
