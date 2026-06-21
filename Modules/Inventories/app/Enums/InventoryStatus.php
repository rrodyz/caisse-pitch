<?php

namespace Modules\Inventories\app\Enums;

enum InventoryStatus: string
{
    case Draft      = 'draft';
    case InProgress = 'in_progress';
    case Validated  = 'validated';
    case Cancelled  = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Draft      => 'Brouillon',
            self::InProgress => 'En cours',
            self::Validated  => 'Validé',
            self::Cancelled  => 'Annulé',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft      => 'gray',
            self::InProgress => 'blue',
            self::Validated  => 'green',
            self::Cancelled  => 'red',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Draft      => 'bg-night-600 text-night-300',
            self::InProgress => 'bg-blue-500/15 text-blue-300',
            self::Validated  => 'bg-emerald-500/15 text-emerald-300',
            self::Cancelled  => 'bg-red-500/15 text-red-300',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::InProgress]);
    }
}
