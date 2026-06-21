<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:view-losses'])->group(function () {
    Route::get('/losses', fn () => view('losses::index'))->name('losses.index');
});
