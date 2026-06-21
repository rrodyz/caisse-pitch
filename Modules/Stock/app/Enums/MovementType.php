<?php

namespace Modules\Stock\app\Enums;

enum MovementType: string
{
    case PurchaseIn          = 'purchase_in';
    case SaleOut             = 'sale_out';
    case Loss                = 'loss';
    case Break               = 'break';
    case Gift                = 'gift';
    case InventoryAdjustment = 'inventory_adjustment';
    case ManualIn            = 'manual_in';
    case ManualOut           = 'manual_out';

    public function label(): string
    {
        return match($this) {
            self::PurchaseIn          => 'Achat',
            self::SaleOut             => 'Vente',
            self::Loss                => 'Perte',
            self::Break               => 'Casse',
            self::Gift                => 'Offert',
            self::InventoryAdjustment => 'Inventaire',
            self::ManualIn            => 'Entrée manuelle',
            self::ManualOut           => 'Sortie manuelle',
        };
    }

    public function isIn(): bool
    {
        return in_array($this, [self::PurchaseIn, self::ManualIn, self::InventoryAdjustment]);
    }

    public function color(): string
    {
        return match($this) {
            self::PurchaseIn          => 'blue',
            self::SaleOut             => 'indigo',
            self::Loss                => 'red',
            self::Break               => 'orange',
            self::Gift                => 'purple',
            self::InventoryAdjustment => 'gray',
            self::ManualIn            => 'green',
            self::ManualOut           => 'yellow',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::PurchaseIn          => 'bg-blue-500/15 text-blue-300',
            self::SaleOut             => 'bg-indigo-500/15 text-indigo-300',
            self::Loss                => 'bg-red-500/15 text-red-300',
            self::Break               => 'bg-amber-500/15 text-amber-300',
            self::Gift                => 'bg-purple-500/15 text-purple-300',
            self::InventoryAdjustment => 'bg-night-600 text-night-300',
            self::ManualIn            => 'bg-emerald-500/15 text-emerald-300',
            self::ManualOut           => 'bg-yellow-500/15 text-yellow-300',
        };
    }

    public static function options(): array
    {
        return array_map(fn($c) => ['value' => $c->value, 'label' => $c->label()], self::cases());
    }
}
