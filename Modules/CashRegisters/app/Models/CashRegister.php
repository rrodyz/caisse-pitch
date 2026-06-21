<?php

namespace Modules\CashRegisters\app\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CashRegister extends Model
{
    use LogsActivity;

    protected $fillable = ['name', 'location', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function sessions(): HasMany
    {
        return $this->hasMany(CashSession::class);
    }

    public function activeSession(): HasOne
    {
        return $this->hasOne(CashSession::class)->where('status', 'open');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function hasOpenSession(): bool
    {
        return $this->activeSession()->exists();
    }
}
