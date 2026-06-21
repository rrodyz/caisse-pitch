<?php

namespace Modules\Inventories\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Products\app\Models\Product;

class InventoryItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'inventory_id',
        'product_id',
        'theoretical_quantity',
        'counted_quantity',
        'gap',
        'unit_cost',
        'gap_cost',
        'notes',
    ];

    protected $casts = [
        'theoretical_quantity' => 'float',
        'counted_quantity'     => 'float',
        'gap'                  => 'float',
        'unit_cost'            => 'float',
        'gap_cost'             => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $item) {
            if ($item->counted_quantity !== null) {
                $item->gap      = round($item->counted_quantity - $item->theoretical_quantity, 4);
                $item->gap_cost = round($item->gap * ($item->unit_cost ?? 0), 2);
            }
        });
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
