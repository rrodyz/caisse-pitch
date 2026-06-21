<?php

namespace Modules\CashRegisters\app\Enums;

enum CashSessionStatus: string
{
    case Open   = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match($this) {
            self::Open   => 'Ouverte',
            self::Closed => 'Clôturée',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Open   => 'green',
            self::Closed => 'gray',
        };
    }
}
