<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:manage-inventory'])->group(function () {
    Route::get('/inventories', fn () => view('inventories::index'))->name('inventories.index');
});
