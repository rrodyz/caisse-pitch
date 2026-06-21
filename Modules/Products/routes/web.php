<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:view-products'])->group(function () {
    Route::get('/products', fn () => view('products::index'))->name('products.index');
});
