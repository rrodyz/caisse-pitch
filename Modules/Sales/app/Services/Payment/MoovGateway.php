<?php

namespace Modules\Sales\app\Services\Payment;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Sales\app\Models\PaymentTransaction;
use RuntimeException;

/**
 * Moov Money (Burkina Faso) — paiement par push USSD.
 * Le client reçoit une demande sur son téléphone et valide avec son code PIN.
 * Pas d'URL/QR : on initie avec le n° de téléphone puis on poll le statut.
 *
 * NOTE : les chemins/champs exacts dépendent du contrat marchand Moov.
 * Paramétrables via config/payments.php (token_path, request_path, status_path).
 */
class MoovGateway implements PaymentGateway
{
    public function key(): string { return 'moov'; }

    public function isEnabled(): bool
    {
        return (bool) config('payments.providers.moov.enabled')
            && filled(config('payments.providers.moov.base_url'))
            && filled(config('payments.providers.moov.username'));
    }

    private function cfg(string $k)
    {
        return config("payments.providers.moov.$k");
    }

    private function token(): string
    {
        return Cache::remember('moov_token', now()->addMinutes(50), function () {
            $resp = Http::asForm()
                ->withBasicAuth($this->cfg('username'), $this->cfg('password'))
                ->acceptJson()
                ->timeout(20)
                ->post(rtrim($this->cfg('base_url'), '/') . $this->cfg('token_path'), [
                    'grant_type' => 'client_credentials',
                ]);

            $token = $resp->json()['access_token'] ?? $resp->json()['token'] ?? null;
            if (! $resp->successful() || ! $token) {
                Log::warning('Moov token failed', ['status' => $resp->status(), 'body' => $resp->body()]);
                throw new RuntimeException('Moov Money : authentification échouée.');
            }

            return $token;
        });
    }

    private function api()
    {
        return Http::withToken($this->token())
            ->acceptJson()
            ->timeout(25)
            ->baseUrl(rtrim($this->cfg('base_url'), '/'));
    }

    public function initiate(PaymentTransaction $tx): PaymentTransaction
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException('Moov Money n\'est pas configuré (clés marchand manquantes).');
        }

        if (! $tx->customer_phone) {
            throw new RuntimeException('Numéro de téléphone du client requis pour Moov Money.');
        }

        $resp = $this->api()->post($this->cfg('request_path'), [
            'amount'        => (int) round($tx->amount),
            'currency'      => $tx->currency ?: 'XOF',
            'externalId'    => $tx->client_reference,
            'reference'     => $tx->client_reference,
            'merchant_code' => $this->cfg('merchant_code'),
            'msisdn'        => $tx->customer_phone,
            'payer'         => ['partyIdType' => 'MSISDN', 'partyId' => $tx->customer_phone],
            'payerMessage'  => 'Paiement ' . $tx->client_reference,
        ]);

        if (! $resp->successful()) {
            Log::warning('Moov requesttopay failed', ['status' => $resp->status(), 'body' => $resp->body()]);
            $tx->update(['status' => PaymentTransaction::FAILED, 'payload' => $resp->json() ?: ['raw' => $resp->body()]]);
            throw new RuntimeException('Échec de la demande de paiement Moov Money (' . $resp->status() . ').');
        }

        $data = $resp->json();
        // id de transaction renvoyé (champ variable selon l'implémentation Moov)
        $ref = $data['referenceId'] ?? $data['transactionId'] ?? $data['transaction_id'] ?? $tx->client_reference;

        $tx->update([
            'external_id' => $ref,
            'status'      => PaymentTransaction::PENDING,
            'payload'     => $data,
        ]);

        return $tx->refresh();
    }

    public function refresh(PaymentTransaction $tx): PaymentTransaction
    {
        if (! $tx->external_id || $tx->isFinished()) {
            return $tx;
        }

        $resp = $this->api()->get(rtrim($this->cfg('status_path'), '/') . '/' . $tx->external_id);

        if (! $resp->successful()) {
            return $tx;
        }

        $status = strtoupper((string) ($resp->json()['status'] ?? ''));
        $mapped = match ($status) {
            'SUCCESSFUL', 'SUCCESS', 'COMPLETED' => PaymentTransaction::SUCCEEDED,
            'FAILED', 'REJECTED'                 => PaymentTransaction::FAILED,
            'EXPIRED', 'TIMEOUT'                 => PaymentTransaction::EXPIRED,
            'CANCELLED'                          => PaymentTransaction::CANCELLED,
            default                              => PaymentTransaction::PENDING,
        };

        $tx->update([
            'status'  => $mapped,
            'payload' => $resp->json(),
            'paid_at' => ($mapped === PaymentTransaction::SUCCEEDED && ! $tx->paid_at) ? now() : $tx->paid_at,
        ]);

        return $tx->refresh();
    }

    public function verifyWebhook(string $rawBody, array $headers): ?array
    {
        return json_decode($rawBody, true) ?: null;
    }
}
