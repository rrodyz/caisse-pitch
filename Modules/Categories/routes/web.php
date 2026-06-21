<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:view-categories'])->group(function () {
    Route::get('/categories', fn () => view('categories::index'))->name('categories.index');
});
