<?php

namespace Modules\Stock\app\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Products\app\Models\Product;
use Modules\Stock\app\Enums\MovementType;
use Modules\Stock\app\Models\StockMovement;

class StockService
{
    /**
     * Ajoute du stock (achat, entrée manuelle).
     */
    public function addStock(
        int    $productId,
        float  $quantity,
        MovementType $type = MovementType::ManualIn,
        ?float $unitCost   = null,
        string $notes      = '',
        ?object $reference = null,
    ): StockMovement {
        return $this->record($productId, $quantity, $type, $unitCost, $notes, $reference, sign: +1);
    }

    /**
     * Déduit du stock (vente, perte, casse, offert).
     */
    public function deductStock(
        int    $productId,
        float  $quantity,
        MovementType $type = MovementType::SaleOut,
        ?float $unitCost   = null,
        string $notes      = '',
        ?object $reference = null,
    ): StockMovement {
        return $this->record($productId, $quantity, $type, $unitCost, $notes, $reference, sign: -1);
    }

    /**
     * Ajustement inventaire : pose une nouvelle valeur absolue.
     */
    public function adjustStock(int $productId, float $newQuantity, string $notes = ''): StockMovement
    {
        return DB::transaction(function () use ($productId, $newQuantity, $notes) {
            $product = Product::lockForUpdate()->findOrFail($productId);
            $before  = (float) $product->stock_quantity;
            $delta   = $newQuantity - $before;

            $product->update(['stock_quantity' => $newQuantity]);

            return StockMovement::create([
                'product_id'      => $productId,
                'type'            => MovementType::InventoryAdjustment,
                'quantity'        => abs($delta),
                'quantity_before' => $before,
                'quantity_after'  => $newQuantity,
                'notes'           => $notes ?: 'Ajustement inventaire',
                'user_id'         => Auth::id(),
            ]);
        });
    }

    /**
     * Produits sous le seuil minimum.
     */
    public function getLowStockProducts()
    {
        return Product::active()
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->orderBy('stock_quantity')
            ->get();
    }

    // ── Interne ──────────────────────────────────────────────────────────────

    private function record(
        int    $productId,
        float  $quantity,
        MovementType $type,
        ?float $unitCost,
        string $notes,
        ?object $reference,
        int    $sign,
    ): StockMovement {
        return DB::transaction(function () use ($productId, $quantity, $type, $unitCost, $notes, $reference, $sign) {
            $product = Product::lockForUpdate()->findOrFail($productId);
            $before  = (float) $product->stock_quantity;
            $after   = max(0, $before + ($sign * $quantity));

            $product->update(['stock_quantity' => $after]);

            return StockMovement::create([
                'product_id'      => $productId,
                'type'            => $type,
                'quantity'        => $quantity,
                'quantity_before' => $before,
                'quantity_after'  => $after,
                'unit_cost'       => $unitCost,
                'reference_type'  => $reference ? get_class($reference) : null,
                'reference_id'    => $reference?->id,
                'notes'           => $notes ?: null,
                'user_id'         => Auth::id(),
            ]);
        });
    }
}
