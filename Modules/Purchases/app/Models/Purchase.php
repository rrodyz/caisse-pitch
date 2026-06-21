<?php

namespace Modules\Purchases\app\Models;

use App\Events\PurchaseValidated;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Suppliers\app\Models\Supplier;

class Purchase extends Model
{
    use LogsActivity;

    protected $fillable = [
        'number', 'date', 'supplier_id', 'status', 'payment_mode', 'payment_status',
        'subtotal', 'fees', 'total_amount', 'notes', 'receipt_path',
        'validated_by', 'validated_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date'         => 'date',
            'validated_at' => 'datetime',
            'subtotal'     => 'float',
            'fees'         => 'float',
            'total_amount' => 'float',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public static function generateNumber(): string
    {
        $year  = now()->year;
        $count = static::whereYear('date', $year)->count();
        return 'ACH-' . $year . '-' . str_pad($count + 1, 6, '0', STR_PAD_LEFT);
    }

    public function validate(int $userId): void
    {
        if ($this->status !== 'draft') {
            throw new \RuntimeException("Seul un achat en brouillon peut être validé.");
        }

        $this->update([
            'status'       => 'validated',
            'validated_by' => $userId,
            'validated_at' => now(),
        ]);

        event(new PurchaseValidated($this->load('items.product')));

        $this->logCustomActivity('validated', 'Achat validé', [
            'total' => $this->total_amount,
        ]);
    }

    public function cancel(): void
    {
        if ($this->status === 'cancelled') {
            return;
        }
        $this->update(['status' => 'cancelled']);
        $this->logCustomActivity('cancelled', 'Achat annulé');
    }

    public function isDraft(): bool    { return $this->status === 'draft'; }
    public function isValidated(): bool { return $this->status === 'validated'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }

    public function scopeDraft($q)     { return $q->where('status', 'draft'); }
    public function scopeValidated($q) { return $q->where('status', 'validated'); }
}
