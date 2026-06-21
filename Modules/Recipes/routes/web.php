<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:view-recipes'])->group(function () {
    Route::get('/recipes', fn () => view('recipes::index'))->name('recipes.index');
});
