<?php

namespace Modules\Losses\app\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Losses\app\Enums\LossType;
use Modules\Products\app\Models\Product;
use Modules\Stock\app\Models\StockMovement;

class Loss extends Model
{
    use LogsActivity;

    protected $fillable = [
        'type',
        'product_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'reason',
        'notes',
        'declared_by',
        'stock_movement_id',
    ];

    protected $casts = [
        'type'       => LossType::class,
        'quantity'   => 'float',
        'unit_cost'  => 'float',
        'total_cost' => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $loss) {
            $loss->total_cost = round(($loss->unit_cost ?? 0) * $loss->quantity, 2);
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function declaredBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'declared_by');
    }

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }
}
