<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:view-users'])->group(function () {
    Route::get('/users', fn () => view('users::index'))->name('users.index');
});

Route::middleware(['auth', 'permission:manage-roles'])->group(function () {
    Route::get('/roles', fn () => view('users::roles'))->name('roles.index');
});

Route::middleware(['auth', 'permission:view-activity-logs'])->group(function () {
    Route::get('/activity-log', fn () => view('users::activity-log'))->name('activity-log.index');
});
