<?php

namespace Modules\Categories\app\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'color',
        'pos_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'pos_order' => 'integer',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(\Modules\Products\app\Models\Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('pos_order')->orderBy('name');
    }
}
