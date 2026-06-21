<?php

namespace Modules\Sales\app\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name', 'phone', 'email',
        'credit_limit', 'current_credit',
        'notes', 'is_active',
    ];

    protected $casts = [
        'credit_limit'   => 'float',
        'current_credit' => 'float',
        'is_active'      => 'boolean',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function creditPayments(): HasMany
    {
        return $this->hasMany(CreditPayment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function hasCredit(): bool
    {
        return $this->current_credit > 0;
    }

    public function availableCredit(): float
    {
        return max(0, $this->credit_limit - $this->current_credit);
    }
}
