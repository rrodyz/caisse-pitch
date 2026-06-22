<?php

namespace Modules\Sales\app\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\CashRegisters\app\Models\CashSession;
use Modules\Sales\app\Enums\PaymentMode;
use Modules\Sales\app\Enums\SaleStatus;

class Sale extends Model
{
    use LogsActivity;

    protected $fillable = [
        'number', 'cash_session_id', 'customer_id', 'served_by', 'status',
        'payment_mode', 'payment_status',
        'subtotal', 'discount_amount', 'total_amount', 'amount_received',
        'notes', 'cancelled_by', 'cancelled_at', 'cancel_reason',
    ];

    protected $casts = [
        'status'         => SaleStatus::class,
        'payment_mode'   => PaymentMode::class,
        'subtotal'        => 'float',
        'discount_amount' => 'float',
        'total_amount'    => 'float',
        'amount_received' => 'float',
        'cancelled_at'   => 'datetime',
    ];

    public static function generateNumber(): string
    {
        $year  = now()->year;
        $count = static::whereYear('created_at', $year)->count();
        return 'VTE-' . $year . '-' . str_pad($count + 1, 6, '0', STR_PAD_LEFT);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    public function servedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'served_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'cancelled_by');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
