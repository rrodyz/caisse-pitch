<?php

namespace Modules\Sales\app\Services\Payment;

use InvalidArgumentException;

class PaymentManager
{
    /** @var array<string, class-string<PaymentGateway>> */
    private array $map = [
        'wave'   => WaveGateway::class,
        'orange' => OrangeGateway::class,
        'moov'   => MoovGateway::class,
    ];

    /** @var array<string, PaymentGateway> */
    private array $resolved = [];

    public function gateway(string $provider): PaymentGateway
    {
        if (! isset($this->map[$provider])) {
            throw new InvalidArgumentException("Provider de paiement inconnu : {$provider}");
        }

        return $this->resolved[$provider] ??= app($this->map[$provider]);
    }

    /** @return array<string> providers actifs */
    public function enabled(): array
    {
        return array_values(array_filter(
            array_keys($this->map),
            fn ($p) => $this->gateway($p)->isEnabled()
        ));
    }
}
