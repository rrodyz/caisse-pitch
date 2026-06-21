<?php

namespace Modules\Sales\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Sales\app\Models\PaymentTransaction;
use Modules\Sales\app\Services\Payment\PaymentManager;

class WaveWebhookController
{
    public function __invoke(Request $request, PaymentManager $manager)
    {
        $gateway = $manager->gateway('wave');
        $event   = $gateway->verifyWebhook($request->getContent(), $request->headers->all());

        if ($event === null) {
            Log::warning('Wave webhook: signature invalide ou secret manquant.');
            return response()->json(['ok' => false], 403);
        }

        $data       = $event['data'] ?? [];
        $externalId = $data['id'] ?? null;

        if ($externalId) {
            $tx = PaymentTransaction::where('provider', 'wave')
                ->where('external_id', $externalId)
                ->first();

            // Source de vérité = polling côté POS ; le webhook ne fait que rafraîchir le statut.
            if ($tx && ! $tx->isFinished()) {
                $gateway->refresh($tx);
            }
        }

        return response()->json(['ok' => true]);
    }
}
