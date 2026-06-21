<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\app\Http\Controllers\StockReportPdfController;
use Modules\Reports\app\Http\Controllers\SalesReportPdfController;
use Modules\Reports\app\Http\Controllers\LossReportPdfController;
use Modules\Reports\app\Http\Controllers\PaymentReportPdfController;

Route::middleware(['auth', 'permission:view-reports'])->group(function () {
    Route::get('/reports/sales',      fn () => view('reports::sales'))->name('reports.sales');
    Route::get('/reports/sales/pdf',  SalesReportPdfController::class)->name('reports.sales.pdf');
    Route::get('/reports/stock',      fn () => view('reports::stock'))->name('reports.stock');
    Route::get('/reports/stock/pdf',  StockReportPdfController::class)->name('reports.stock.pdf');
    Route::get('/reports/losses',       fn () => view('reports::losses'))->name('reports.losses');
    Route::get('/reports/losses/pdf',   LossReportPdfController::class)->name('reports.losses.pdf');
    Route::get('/reports/payments',     fn () => view('reports::payments'))->name('reports.payments');
    Route::get('/reports/payments/pdf', PaymentReportPdfController::class)->name('reports.payments.pdf');
});
