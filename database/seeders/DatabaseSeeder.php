<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            \Modules\Settings\database\seeders\SettingsSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
