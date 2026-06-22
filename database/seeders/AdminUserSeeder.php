<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email    = env('ADMIN_EMAIL', 'admin@caisse-pitch.local');
        $password = env('ADMIN_PASSWORD');

        if (! $password) {
            if (app()->isProduction()) {
                $this->command->error('ADMIN_PASSWORD manquant dans .env — compte admin non créé.');
                return;
            }
            // Dev : mot de passe généré aléatoirement, affiché une seule fois
            $password = Str::password(16);
            $this->command->warn("ADMIN_PASSWORD non défini — mdp généré : {$password}");
        }

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'first_name'         => 'Admin',
                'last_name'          => 'Système',
                'username'           => 'admin',
                'phone'              => null,
                'password'           => Hash::make($password),
                'is_active'          => true,
                'email_verified_at'  => now(),
            ]
        );

        if ($admin->roles->isEmpty()) {
            $admin->assignRole('Administrateur');
        }
    }
}
