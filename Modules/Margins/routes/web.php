<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:view-reports'])->group(function () {
    Route::get('/margins', fn () => view('margins::margins'))->name('margins.index');
});
