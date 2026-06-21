<?php

namespace Modules\CashRegisters\app\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\CashRegisters\app\Enums\CashSessionStatus;

class CashSession extends Model
{
    use LogsActivity;

    protected $fillable = [
        'cash_register_id',
        'opened_by',
        'closed_by',
        'status',
        'opening_amount',
        'closing_amount',
        'expected_amount',
        'gap',
        'notes_opening',
        'notes_closing',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'status'          => CashSessionStatus::class,
        'opening_amount'  => 'float',
        'closing_amount'  => 'float',
        'expected_amount' => 'float',
        'gap'             => 'float',
        'opened_at'       => 'datetime',
        'closed_at'       => 'datetime',
    ];

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'closed_by');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', CashSessionStatus::Open);
    }

    public function isOpen(): bool
    {
        return $this->status === CashSessionStatus::Open;
    }

    public function duration(): string
    {
        $end = $this->closed_at ?? now();
        return $this->opened_at->diffForHumans($end, true);
    }
}
