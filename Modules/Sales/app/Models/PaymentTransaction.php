<?php

namespace Modules\Sales\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'sale_id', 'provider', 'status', 'amount', 'currency',
        'client_reference', 'external_id', 'checkout_url',
        'customer_phone', 'payload', 'paid_at',
    ];

    protected $casts = [
        'amount'  => 'float',
        'payload' => 'array',
        'paid_at' => 'datetime',
    ];

    public const PENDING   = 'pending';
    public const SUCCEEDED = 'succeeded';
    public const FAILED    = 'failed';
    public const CANCELLED = 'cancelled';
    public const EXPIRED   = 'expired';

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function isSucceeded(): bool
    {
        return $this->status === self::SUCCEEDED;
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [self::SUCCEEDED, self::FAILED, self::CANCELLED, self::EXPIRED], true);
    }
}
