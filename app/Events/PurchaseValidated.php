<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Purchases\app\Models\Purchase;

class PurchaseValidated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Purchase $purchase
    ) {}
}
