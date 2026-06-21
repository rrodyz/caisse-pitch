<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:view-purchases'])->group(function () {
    Route::get('/purchases', fn () => view('purchases::index'))->name('purchases.index');
    Route::get('/purchases/create', fn () => view('purchases::create'))->name('purchases.create')
        ->middleware('permission:create-purchases');
    Route::get('/purchases/{purchase}/edit', function (\Modules\Purchases\app\Models\Purchase $purchase) {
        return view('purchases::edit', compact('purchase'));
    })->name('purchases.edit')->middleware('permission:edit-purchases');
    Route::get('/purchases/{purchase}', function (\Modules\Purchases\app\Models\Purchase $purchase) {
        $purchase->load(['supplier', 'items.product', 'creator', 'validator']);
        return view('purchases::show', compact('purchase'));
    })->name('purchases.show');
});
