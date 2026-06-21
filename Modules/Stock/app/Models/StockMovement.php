<?php

namespace Modules\Stock\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Products\app\Models\Product;
use Modules\Stock\app\Enums\MovementType;

class StockMovement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'unit_cost',
        'reference_type',
        'reference_id',
        'notes',
        'user_id',
        'created_at',
    ];

    protected $casts = [
        'type'            => MovementType::class,
        'quantity'        => 'float',
        'quantity_before' => 'float',
        'quantity_after'  => 'float',
        'unit_cost'       => 'float',
        'created_at'      => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function delta(): float
    {
        return $this->quantity_after - $this->quantity_before;
    }
}
