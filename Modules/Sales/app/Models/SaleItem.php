<?php

namespace Modules\Sales\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Products\app\Models\Product;

class SaleItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sale_id', 'product_id', 'product_name',
        'unit_price', 'quantity', 'discount', 'total_price',
    ];

    protected $casts = [
        'unit_price'  => 'float',
        'quantity'    => 'float',
        'discount'    => 'float',
        'total_price' => 'float',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
