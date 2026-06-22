<?php

namespace Modules\Products\app\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Categories\app\Models\Category;
use Modules\Products\app\Enums\ProductUnit;

class Product extends Model
{
    use LogsActivity;

    protected $fillable = [
        'code',
        'name',
        'category_id',
        'purchase_price',
        'selling_price',
        'margin',
        'margin_rate',
        'markup_rate',
        'stock_quantity',
        'min_stock',
        'unit',
        'image',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'float',
            'selling_price'  => 'float',
            'margin'         => 'float',
            'margin_rate'    => 'float',
            'markup_rate'    => 'float',
            'stock_quantity' => 'float',
            'min_stock'      => 'float',
            'is_active'      => 'boolean',
            'unit'           => ProductUnit::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $product) {
            $buy  = (float) $product->purchase_price;
            $sell = (float) $product->selling_price;

            $product->margin      = $sell - $buy;
            $product->margin_rate = $buy > 0  ? round((($sell - $buy) / $buy)  * 100, 2) : 0;
            $product->markup_rate = $sell > 0 ? round((($sell - $buy) / $sell) * 100, 2) : 0;
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock');
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock;
    }

    public function getActivityHiddenAttributes(): array
    {
        return ['image'];
    }
}
