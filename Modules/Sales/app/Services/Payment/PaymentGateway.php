<?php

namespace Modules\Sales\app\Services\Payment;

use Modules\Sales\app\Models\PaymentTransaction;

interface PaymentGateway
{
    /** Clé du provider (wave|orange|moov). */
    public function key(): string;

    /** Le provider est-il configuré/activé ? */
    public function isEnabled(): bool;

    /**
     * Démarre un encaissement : appelle l'API du provider, renseigne
     * external_id + checkout_url sur la transaction et la sauvegarde.
     */
    public function initiate(PaymentTransaction $tx): PaymentTransaction;

    /**
     * Interroge l'API pour rafraîchir le statut de la transaction (polling).
     */
    public function refresh(PaymentTransaction $tx): PaymentTransaction;

    /**
     * Vérifie la signature d'un webhook et retourne le payload décodé,
     * ou null si invalide.
     */
    public function verifyWebhook(string $rawBody, array $headers): ?array;
}
