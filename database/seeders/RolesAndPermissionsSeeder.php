<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Utilisateurs
            'view-users', 'create-users', 'edit-users', 'delete-users', 'manage-roles',
            // Paramétrage
            'view-settings', 'edit-settings',
            // Catégories
            'view-categories', 'create-categories', 'edit-categories', 'delete-categories',
            // Produits
            'view-products', 'create-products', 'edit-products', 'delete-products',
            // Fournisseurs
            'view-suppliers', 'create-suppliers', 'edit-suppliers', 'delete-suppliers',
            // Achats
            'view-purchases', 'create-purchases', 'edit-purchases', 'validate-purchases',
            // Recettes / Cocktails
            'view-recipes', 'create-recipes', 'edit-recipes', 'delete-recipes',
            // Stock
            'view-stock', 'adjust-stock', 'view-stock-movements',
            'view-losses', 'create-losses', 'edit-losses', 'delete-losses',
            'manage-inventory',
            // Caisses
            'view-cash-registers', 'manage-cash-registers', 'open-cash-session', 'close-cash-session', 'view-cash-sessions',
            // Ventes
            'view-sales', 'create-sales', 'cancel-sales', 'apply-discounts',
            // Clients
            'view-customers', 'create-customers', 'edit-customers', 'delete-customers',
            // Tickets
            'print-tickets', 'reprint-tickets',
            // Rapports
            'view-reports', 'export-reports',
            // Dashboard
            'view-dashboard',
            // Journal
            'view-activity-logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'Administrateur' => array_values($permissions),

            'Gérant' => array_diff($permissions, ['manage-roles', 'delete-users']),

            'Superviseur' => [
                'view-users',
                'view-settings',
                'view-categories', 'view-products',
                'view-suppliers', 'view-purchases', 'validate-purchases',
                'view-recipes',
                'view-stock', 'view-stock-movements', 'view-losses', 'create-losses', 'edit-losses', 'delete-losses', 'manage-inventory',
                'view-cash-registers', 'view-cash-sessions', 'close-cash-session',
                'view-sales', 'cancel-sales',
                'view-customers',
                'reprint-tickets',
                'view-reports', 'export-reports',
                'view-dashboard',
            ],

            'Caissier' => [
                'view-categories', 'view-products',
                'open-cash-session', 'close-cash-session', 'view-cash-sessions',
                'view-sales', 'create-sales',
                'view-customers', 'create-customers',
                'print-tickets', 'reprint-tickets',
                'view-dashboard',
            ],

            'Barman' => [
                'view-categories', 'view-products',
                'view-stock',
                'view-sales', 'create-sales',
                'print-tickets', 'reprint-tickets',
                'view-dashboard',
            ],

            'Serveur' => [
                'view-categories', 'view-products',
                'view-sales', 'create-sales',
                'print-tickets',
                'view-dashboard',
            ],

            'Magasinier' => [
                'view-categories', 'view-products',
                'view-suppliers', 'view-purchases',
                'view-recipes',
                'view-stock', 'adjust-stock', 'view-stock-movements', 'view-losses', 'create-losses', 'edit-losses', 'delete-losses', 'manage-inventory',
                'view-dashboard',
            ],

            'Comptable' => [
                'view-categories', 'view-products',
                'view-purchases',
                'view-stock',
                'view-sales',
                'view-customers',
                'view-reports', 'export-reports',
                'view-dashboard',
            ],

            'Lecteur' => [
                'view-dashboard',
                'view-reports',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions(array_values($rolePermissions));
        }
    }
}
