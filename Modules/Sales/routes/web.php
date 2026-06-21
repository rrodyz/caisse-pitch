<?php

use Illuminate\Support\Facades\Route;

// ── Paiements mobiles (publics : pas d'auth) ──────────────────────────────
// Webhook Wave (CSRF exclu via bootstrap/app.php ; signature HMAC vérifiée)
Route::post('/webhooks/wave', \Modules\Sales\app\Http\Controllers\WaveWebhookController::class)
    ->name('webhooks.wave');

// Notifications Orange / Moov (polling = source de vérité ; on accuse réception)
Route::match(['post', 'get'], '/webhooks/orange', fn () => response()->json(['ok' => true]))
    ->name('webhooks.orange');
Route::match(['post', 'get'], '/webhooks/moov', fn () => response()->json(['ok' => true]))
    ->name('webhooks.moov');

// Page de retour affichée sur le téléphone du client après paiement Wave
Route::get('/payments/wave/return', fn () => view('sales::payment-return', [
    'status' => request('status', 'success'),
]))->name('payments.wave.return');

Route::middleware(['auth'])->group(function () {

    Route::middleware('permission:create-sales')->group(function () {
        Route::get('/pos', fn () => view('sales::pos'))->name('pos.index');
    });

    Route::middleware('permission:view-sales')->group(function () {
        Route::get('/sales',     fn () => view('sales::sales-list'))->name('sales.index');
        Route::get('/customers', fn () => view('sales::customers'))->name('customers.index');
    });

    Route::get('/tickets/{sale}', function (\Modules\Sales\app\Models\Sale $sale) {
        $sale->load(['items', 'servedBy', 'customer']);
        $settings = \Modules\Settings\app\Models\Setting::current();
        return view('sales::ticket', compact('sale', 'settings'));
    })->name('tickets.show');
});
