<?php

namespace Modules\Sales\app\Enums;

enum SaleStatus: string
{
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded  = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::Completed => 'Complétée',
            self::Cancelled => 'Annulée',
            self::Refunded  => 'Remboursée',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Completed => 'green',
            self::Cancelled => 'red',
            self::Refunded  => 'yellow',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Completed => 'bg-emerald-500/15 text-emerald-300',
            self::Cancelled => 'bg-red-500/15 text-red-300',
            self::Refunded  => 'bg-amber-500/15 text-amber-300',
        };
    }
}
