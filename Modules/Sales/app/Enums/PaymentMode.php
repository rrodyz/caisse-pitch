<?php

namespace Modules\Sales\app\Enums;

enum PaymentMode: string
{
    case Cash        = 'cash';
    case Card        = 'card';
    case MobileMoney = 'mobile_money'; // déprécié — conservé pour anciennes ventes
    case OrangeMoney = 'orange_money';
    case MoovMoney   = 'moov_money';
    case Wave        = 'wave';
    case Credit      = 'credit';

    public function label(): string
    {
        return match($this) {
            self::Cash        => 'Espèces',
            self::Card        => 'Carte bancaire',
            self::MobileMoney => 'Mobile Money',
            self::OrangeMoney => 'Orange Money',
            self::MoovMoney   => 'Moov Money',
            self::Wave        => 'Wave',
            self::Credit      => 'Crédit client',
        };
    }

    public function isCash(): bool
    {
        return $this === self::Cash;
    }

    /** Paiement encaissé via une API externe (asynchrone). */
    public function isGateway(): bool
    {
        return in_array($this, [self::OrangeMoney, self::MoovMoney, self::Wave], true);
    }

    /** Clé du provider de paiement (config/payments.php). */
    public function provider(): ?string
    {
        return match($this) {
            self::OrangeMoney => 'orange',
            self::MoovMoney   => 'moov',
            self::Wave        => 'wave',
            default           => null,
        };
    }

    /** Couleur d'accent pour l'UI (hex). */
    public function color(): string
    {
        return match($this) {
            self::OrangeMoney => '#ff7900',
            self::MoovMoney   => '#0a6cff',
            self::Wave        => '#1dc8ec',
            self::Credit      => '#f59e0b',
            default           => '#8b5cf6',
        };
    }

    /** Modes sélectionnables au POS (exclut le mobile_money générique déprécié). */
    public static function options(): array
    {
        return array_map(
            fn($c) => ['value' => $c->value, 'label' => $c->label(), 'gateway' => $c->isGateway(), 'color' => $c->color()],
            array_filter(self::cases(), fn($c) => $c !== self::MobileMoney)
        );
    }
}
