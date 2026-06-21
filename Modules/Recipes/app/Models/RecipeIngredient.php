<?php

namespace Modules\Recipes\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Products\app\Models\Product;

class RecipeIngredient extends Model
{
    protected $fillable = ['recipe_id', 'product_id', 'quantity'];

    protected function casts(): array
    {
        return ['quantity' => 'float'];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function lineCost(): float
    {
        return ($this->product?->purchase_price ?? 0) * $this->quantity;
    }
}
