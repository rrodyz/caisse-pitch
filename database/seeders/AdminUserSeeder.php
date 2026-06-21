<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@caisse-pitch.local'],
            [
                'first_name' => 'Admin',
                'last_name'  => 'Système',
                'username'   => 'admin',
                'phone'      => null,
                'password'   => Hash::make('Admin@2024!'),
                'is_active'  => true,
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('Administrateur');
    }
}
