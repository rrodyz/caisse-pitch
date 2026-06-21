<?php

namespace Modules\Inventories\app\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Inventories\app\Enums\InventoryStatus;

class Inventory extends Model
{
    use LogsActivity;

    protected $fillable = [
        'reference',
        'status',
        'notes',
        'started_by',
        'validated_by',
        'started_at',
        'validated_at',
    ];

    protected $casts = [
        'status'       => InventoryStatus::class,
        'started_at'   => 'datetime',
        'validated_at' => 'datetime',
    ];

    public static function generateReference(): string
    {
        $year  = now()->year;
        $count = static::whereYear('created_at', $year)->count();
        return 'INV-' . $year . '-' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'started_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'validated_by');
    }

    public function totalGapCost(): float
    {
        return (float) $this->items()->sum('gap_cost');
    }

    public function countedItems(): int
    {
        return $this->items()->whereNotNull('counted_quantity')->count();
    }
}
