<?php

namespace Modules\Sales\app\Services\Payment;

use Modules\Sales\app\Models\PaymentTransaction;
use Modules\Sales\app\Models\Sale;

class PaymentService
{
    public function __construct(private PaymentManager $manager) {}

    /**
     * Démarre un encaissement gateway AVANT création de la vente.
     * La vente n'est créée qu'au succès du paiement (pas de vente fantôme).
     */
    public function start(float $amount, string $reference, string $provider, ?string $phone = null): PaymentTransaction
    {
        $tx = PaymentTransaction::create([
            'provider'         => $provider,
            'status'           => PaymentTransaction::PENDING,
            'amount'           => $amount,
            'currency'         => config('payments.currency', 'XOF'),
            'client_reference' => $reference,
            'customer_phone'   => $phone,
        ]);

        return $this->manager->gateway($provider)->initiate($tx);
    }

    /** Rafraîchit le statut de la transaction (polling). */
    public function poll(PaymentTransaction $tx): PaymentTransaction
    {
        return $this->manager->gateway($tx->provider)->refresh($tx);
    }

    /** Lie la transaction à la vente créée après paiement réussi. */
    public function attachSale(PaymentTransaction $tx, Sale $sale): void
    {
        $tx->update(['sale_id' => $sale->id]);
    }
}
