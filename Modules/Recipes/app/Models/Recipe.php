<?php

namespace Modules\Recipes\app\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Products\app\Models\Product;

class Recipe extends Model
{
    use LogsActivity;

    protected $fillable = [
        'product_id',
        'description',
        'cost_price',
        'margin',
        'margin_rate',
        'markup_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cost_price'  => 'float',
            'margin'      => 'float',
            'margin_rate' => 'float',
            'markup_rate' => 'float',
            'is_active'   => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function recalculate(): void
    {
        $this->load('ingredients.product');

        $cost = $this->ingredients->sum(
            fn($i) => ($i->product?->purchase_price ?? 0) * $i->quantity
        );

        $sell = $this->product?->selling_price ?? 0;
        $margin      = $sell - $cost;
        $margin_rate = $cost > 0  ? round(($margin / $cost) * 100, 2) : 0;
        $markup_rate = $sell > 0  ? round(($margin / $sell) * 100, 2) : 0;

        $this->updateQuietly([
            'cost_price'  => round($cost, 2),
            'margin'      => round($margin, 2),
            'margin_rate' => $margin_rate,
            'markup_rate' => $markup_rate,
        ]);
    }
}
