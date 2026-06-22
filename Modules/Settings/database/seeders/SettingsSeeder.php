<?php

namespace Modules\Settings\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Settings\app\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::firstOrCreate([], [
            'establishment_name'             => env('ESTABLISHMENT_NAME', 'Mon Établissement'),
            'currency'                       => 'FCFA',
            'currency_code'                  => 'XOF',
            'ticket_message'                 => 'Merci de votre visite !',
            'tax_rate'                       => 0,
            'ticket_number_prefix'           => 'TKT',
            'ticket_number_padding'          => 6,
            'stock_alert_threshold'          => 5,
            'max_discount_percent'           => 10,
            'supervisor_approval_threshold'  => null,
        ]);
    }
}
