<?php

namespace Modules\Settings\app\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use LogsActivity;

    protected $fillable = [
        'establishment_name',
        'logo',
        'address',
        'phone',
        'email',
        'currency',
        'currency_code',
        'ticket_message',
        'tax_rate',
        'ticket_number_prefix',
        'ticket_number_padding',
        'stock_alert_threshold',
        'max_discount_percent',
        'supervisor_approval_threshold',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate'                       => 'float',
            'ticket_number_padding'          => 'integer',
            'stock_alert_threshold'          => 'integer',
            'max_discount_percent'           => 'float',
            'supervisor_approval_threshold'  => 'float',
        ];
    }

    public static function current(): static
    {
        return Cache::rememberForever('app_settings', fn () => static::firstOrCreate([]));
    }

    public static function clearCache(): void
    {
        Cache::forget('app_settings');
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return static::current()->{$key} ?? $default;
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::clearCache());
    }

    public function getActivityHiddenAttributes(): array
    {
        return [];
    }
}
