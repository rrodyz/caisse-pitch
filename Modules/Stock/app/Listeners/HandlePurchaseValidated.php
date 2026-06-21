<?php

namespace Modules\Stock\app\Listeners;

use App\Events\PurchaseValidated;
use Modules\Stock\app\Enums\MovementType;
use Modules\Stock\app\Services\StockService;

class HandlePurchaseValidated
{
    public function __construct(private StockService $stock) {}

    public function handle(PurchaseValidated $event): void
    {
        $purchase = $event->purchase;

        foreach ($purchase->items as $item) {
            if (! $item->product_id) {
                continue;
            }

            $this->stock->addStock(
                productId: $item->product_id,
                quantity:  (float) $item->quantity,
                type:      MovementType::PurchaseIn,
                unitCost:  (float) $item->unit_price,
                notes:     "Achat {$purchase->number}",
                reference: $purchase,
            );
        }
    }
}
