<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffUsersSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = env('STAFF_DEFAULT_PASSWORD', 'ChangeMe@2024!');

        $users = [
            [
                'first_name' => 'Yves',
                'last_name'  => 'Gbaguidi',
                'username'   => 'gerant',
                'email'      => 'gerant@caisse-pitch.local',
                'phone'      => '+229 97 11 22 33',
                'role'       => 'Gérant',
            ],
            [
                'first_name' => 'Fabrice',
                'last_name'  => 'Hounkpè',
                'username'   => 'caissier1',
                'email'      => 'caissier@caisse-pitch.local',
                'phone'      => '+229 97 44 55 66',
                'role'       => 'Caissier',
            ],
            [
                'first_name' => 'Romuald',
                'last_name'  => 'Akodegnon',
                'username'   => 'barman1',
                'email'      => 'barman@caisse-pitch.local',
                'phone'      => '+229 97 77 88 99',
                'role'       => 'Barman',
            ],
            [
                'first_name' => 'Cécile',
                'last_name'  => 'Dossevi',
                'username'   => 'superviseur1',
                'email'      => 'superviseur@caisse-pitch.local',
                'phone'      => null,
                'role'       => 'Superviseur',
            ],
            [
                'first_name' => 'Ibrahim',
                'last_name'  => 'Soumanou',
                'username'   => 'magasinier1',
                'email'      => 'magasinier@caisse-pitch.local',
                'phone'      => null,
                'role'       => 'Magasinier',
            ],
        ];

        $hashedPassword = Hash::make($defaultPassword);

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password'          => $hashedPassword,
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ])
            );

            if ($user->roles->isEmpty()) {
                $user->assignRole($role);
            }
        }

        $this->command->info('Comptes staff créés — mot de passe par défaut : ' . $defaultPassword);
        $this->command->warn('Changez les mots de passe via l\'interface ou définissez STAFF_DEFAULT_PASSWORD dans .env.');
    }
}
