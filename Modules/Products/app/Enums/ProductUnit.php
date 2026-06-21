<?php

namespace Modules\Products\app\Enums;

enum ProductUnit: string
{
    case Bouteille = 'bouteille';
    case Verre     = 'verre';
    case Canette   = 'canette';
    case Carton    = 'carton';
    case Unite     = 'unité';

    public function label(): string
    {
        return match($this) {
            self::Bouteille => 'Bouteille',
            self::Verre     => 'Verre',
            self::Canette   => 'Canette',
            self::Carton    => 'Carton',
            self::Unite     => 'Unité',
        };
    }

    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
