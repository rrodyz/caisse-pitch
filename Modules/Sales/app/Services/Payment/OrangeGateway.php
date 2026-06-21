<?php

namespace Modules\Sales\app\Services\Payment;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Sales\app\Models\PaymentTransaction;
use RuntimeException;

/**
 * Orange Money (Burkina Faso) — Web Payment API.
 * Docs: https://developer.orange.com — OAuth client_credentials puis
 * /orange-money-webpay/{country}/v1/webpayment (+ /transactionstatus).
 */
class OrangeGateway implements PaymentGateway
{
    public function key(): string { return 'orange'; }

    public function isEnabled(): bool
    {
        return (bool) config('payments.providers.orange.enabled')
            && filled(config('payments.providers.orange.client_id'))
            && filled(config('payments.providers.orange.merchant_key'));
    }

    private function cfg(string $k)
    {
        return config("payments.providers.orange.$k");
    }

    /** Jeton OAuth (mis en cache jusqu'à expiration). */
    private function token(): string
    {
        return Cache::remember('orange_om_token', now()->addMinutes(50), function () {
            $resp = Http::asForm()
                ->withBasicAuth($this->cfg('client_id'), $this->cfg('client_secret'))
                ->acceptJson()
                ->timeout(20)
                ->post(rtrim($this->cfg('base_url'), '/') . '/oauth/v3/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if (! $resp->successful() || ! ($resp->json()['access_token'] ?? null)) {
                Log::warning('Orange OAuth failed', ['status' => $resp->status(), 'body' => $resp->body()]);
                throw new RuntimeException('Orange Money : authentification échouée.');
            }

            return $resp->json()['access_token'];
        });
    }

    private function api()
    {
        return Http::withToken($this->token())
            ->acceptJson()
            ->timeout(25)
            ->baseUrl(rtrim($this->cfg('base_url'), '/') . '/orange-money-webpay/' . $this->cfg('country') . '/v1');
    }

    public function initiate(PaymentTransaction $tx): PaymentTransaction
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException('Orange Money n\'est pas configuré (clés marchand manquantes).');
        }

        $base = rtrim(config('payments.callback_url') ?: config('app.url'), '/');

        $resp = $this->api()->post('/webpayment', [
            'merchant_key' => $this->cfg('merchant_key'),
            'currency'     => $this->cfg('currency'),
            'order_id'     => $tx->client_reference,
            'amount'       => (int) round($tx->amount),
            'lang'         => $this->cfg('lang'),
            'reference'    => $tx->client_reference,
            'return_url'   => $base . '/payments/wave/return?status=success',
            'cancel_url'   => $base . '/payments/wave/return?status=error',
            'notif_url'    => $base . '/webhooks/orange',
        ]);

        if (! $resp->successful() || ! ($resp->json()['payment_url'] ?? null)) {
            Log::warning('Orange webpayment failed', ['status' => $resp->status(), 'body' => $resp->body()]);
            $tx->update(['status' => PaymentTransaction::FAILED, 'payload' => $resp->json() ?: ['raw' => $resp->body()]]);
            throw new RuntimeException('Échec de création du paiement Orange Money (' . $resp->status() . ').');
        }

        $data = $resp->json();

        $tx->update([
            'external_id'  => $data['pay_token'] ?? null,
            'checkout_url' => $data['payment_url'] ?? null,
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

        $resp = $this->api()->post('/transactionstatus', [
            'order_id'  => $tx->client_reference,
            'amount'    => (int) round($tx->amount),
            'pay_token' => $tx->external_id,
        ]);

        if (! $resp->successful()) {
            return $tx;
        }

        $status = strtoupper((string) ($resp->json()['status'] ?? ''));
        $mapped = match ($status) {
            'SUCCESS', 'SUCCESSFUL' => PaymentTransaction::SUCCEEDED,
            'FAILED', 'NOTFOUND'    => PaymentTransaction::FAILED,
            'EXPIRED'               => PaymentTransaction::EXPIRED,
            'CANCELLED'             => PaymentTransaction::CANCELLED,
            default                 => PaymentTransaction::PENDING,
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
        // Orange notifie sur notif_url ; le statut réel est confirmé par /transactionstatus (polling).
        return json_decode($rawBody, true) ?: null;
    }
}
