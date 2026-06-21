<?php

namespace Modules\Sales\app\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Sales\app\Models\PaymentTransaction;
use RuntimeException;

/**
 * Intégration Wave Checkout API.
 * Docs: https://docs.wave.com/business — POST /v1/checkout/sessions, GET /v1/checkout/sessions/{id}
 */
class WaveGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'wave';
    }

    public function isEnabled(): bool
    {
        return (bool) config('payments.providers.wave.enabled')
            && filled(config('payments.providers.wave.api_key'));
    }

    private function client()
    {
        $cfg = config('payments.providers.wave');

        return Http::baseUrl(rtrim($cfg['base_url'], '/'))
            ->withToken($cfg['api_key'])
            ->acceptJson()
            ->timeout(20);
    }

    public function initiate(PaymentTransaction $tx): PaymentTransaction
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException('Wave n\'est pas configuré (WAVE_API_KEY manquant).');
        }

        $base = rtrim(config('payments.callback_url') ?: config('app.url'), '/');

        $resp = $this->client()->post('/v1/checkout/sessions', [
            // XOF : montant entier sans décimales, en chaîne
            'amount'           => (string) (int) round($tx->amount),
            'currency'         => $tx->currency ?: 'XOF',
            'client_reference' => $tx->client_reference,
            'success_url'      => $base . '/payments/wave/return?ref=' . urlencode($tx->client_reference) . '&status=success',
            'error_url'        => $base . '/payments/wave/return?ref=' . urlencode($tx->client_reference) . '&status=error',
        ]);

        if (! $resp->successful()) {
            Log::warning('Wave initiate failed', ['status' => $resp->status(), 'body' => $resp->body()]);
            $tx->update(['status' => PaymentTransaction::FAILED, 'payload' => $resp->json() ?: ['raw' => $resp->body()]]);
            throw new RuntimeException('Échec de création du paiement Wave (' . $resp->status() . ').');
        }

        $data = $resp->json();

        $tx->update([
            'external_id'  => $data['id'] ?? null,
            'checkout_url' => $data['wave_launch_url'] ?? null,
            'status'       => PaymentTransaction::PENDING,
            'payload'      => $data,
        ]);

        return $tx->refresh();
    }

    public function refresh(PaymentTransaction $tx): PaymentTransaction
    {
        if (! $tx->external_id || $tx->isFinished()) {
            return $tx;
        }

        $resp = $this->client()->get('/v1/checkout/sessions/' . $tx->external_id);

        if (! $resp->successful()) {
            return $tx;
        }

        $data = $resp->json();
        $tx->update([
            'status'  => $this->mapStatus($data),
            'payload' => $data,
            'paid_at' => ($this->mapStatus($data) === PaymentTransaction::SUCCEEDED && ! $tx->paid_at) ? now() : $tx->paid_at,
        ]);

        return $tx->refresh();
    }

    /**
     * Wave: payment_status = processing|succeeded|cancelled ; checkout_status = open|complete|expired
     */
    private function mapStatus(array $data): string
    {
        $pay = $data['payment_status'] ?? null;
        $checkout = $data['checkout_status'] ?? null;

        if ($pay === 'succeeded') return PaymentTransaction::SUCCEEDED;
        if ($pay === 'cancelled') return PaymentTransaction::CANCELLED;
        if ($checkout === 'expired') return PaymentTransaction::EXPIRED;

        return PaymentTransaction::PENDING;
    }

    public function verifyWebhook(string $rawBody, array $headers): ?array
    {
        $secret = config('payments.providers.wave.webhook_secret');
        $header = $headers['wave-signature'][0] ?? ($headers['Wave-Signature'][0] ?? null);

        if (! $secret || ! $header) {
            return null;
        }

        // Header Wave: "t=timestamp, v1=signature"
        $parts = [];
        foreach (explode(',', $header) as $kv) {
            [$k, $v] = array_pad(explode('=', trim($kv), 2), 2, null);
            $parts[$k] = $v;
        }
        $timestamp = $parts['t'] ?? '';
        $given     = $parts['v1'] ?? '';

        $computed = hash_hmac('sha256', $timestamp . $rawBody, $secret);

        if (! hash_equals($computed, (string) $given)) {
            return null;
        }

        return json_decode($rawBody, true);
    }
}
