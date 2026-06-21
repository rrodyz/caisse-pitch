<?php

namespace Modules\Losses\app\Enums;

use Modules\Stock\app\Enums\MovementType;

enum LossType: string
{
    case Perte  = 'loss';
    case Casse  = 'break';
    case Offert = 'gift';

    public function label(): string
    {
        return match($this) {
            self::Perte  => 'Perte',
            self::Casse  => 'Casse',
            self::Offert => 'Offert / Gratuit',
        };
    }

    public function movementType(): MovementType
    {
        return match($this) {
            self::Perte  => MovementType::Loss,
            self::Casse  => MovementType::Break,
            self::Offert => MovementType::Gift,
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Perte  => 'red',
            self::Casse  => 'orange',
            self::Offert => 'purple',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Perte  => 'bg-red-500/15 text-red-300',
            self::Casse  => 'bg-amber-500/15 text-amber-300',
            self::Offert => 'bg-purple-500/15 text-purple-300',
        };
    }

    public static function options(): array
    {
        return array_map(fn($c) => ['value' => $c->value, 'label' => $c->label()], self::cases());
    }
}
