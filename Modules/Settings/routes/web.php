<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\app\Http\Controllers\SettingsController;

Route::middleware(['auth', 'permission:view-settings'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
});
