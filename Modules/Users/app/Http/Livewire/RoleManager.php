<?php

namespace Modules\Users\app\Http\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleManager extends Component
{
    public ?string $selectedRole = null;
    public array   $rolePermissions = [];

    public bool   $showCreateModal = false;
    public string $newRoleName     = '';

    protected const PROTECTED_ROLE = 'Administrateur';

    protected const PERMISSION_GROUPS = [
        'Utilisateurs'  => ['view-users', 'create-users', 'edit-users', 'delete-users', 'manage-roles'],
        'Paramétrage'   => ['view-settings', 'edit-settings'],
        'Catégories'    => ['view-categories', 'create-categories', 'edit-categories', 'delete-categories'],
        'Produits'      => ['view-products', 'create-products', 'edit-products', 'delete-products'],
        'Fournisseurs'  => ['view-suppliers', 'create-suppliers', 'edit-suppliers', 'delete-suppliers'],
        'Achats'        => ['view-purchases', 'create-purchases', 'edit-purchases', 'validate-purchases'],
        'Recettes'      => ['view-recipes', 'create-recipes', 'edit-recipes', 'delete-recipes'],
        'Stock'         => ['view-stock', 'adjust-stock', 'view-stock-movements',
                            'view-losses', 'create-losses', 'edit-losses', 'delete-losses', 'manage-inventory'],
        'Caisses'       => ['view-cash-registers', 'manage-cash-registers',
                            'open-cash-session', 'close-cash-session', 'view-cash-sessions'],
        'Ventes'        => ['view-sales', 'create-sales', 'cancel-sales', 'apply-discounts',
                            'view-customers', 'create-customers', 'edit-customers', 'delete-customers'],
        'Tickets'       => ['print-tickets', 'reprint-tickets'],
        'Rapports'      => ['view-reports', 'export-reports'],
        'Tableau de bord' => ['view-dashboard'],
    ];

    public function selectRole(string $roleName): void
    {
        $this->authorize('manage-roles');
        $this->selectedRole = $roleName;
        $role = Role::where('name', $roleName)->firstOrFail();
        $this->rolePermissions = $role->permissions->pluck('name')->toArray();
    }

    public function savePermissions(): void
    {
        $this->authorize('manage-roles');

        if ($this->selectedRole === self::PROTECTED_ROLE) {
            session()->flash('error', 'Le rôle Administrateur ne peut pas être modifié.');
            return;
        }

        $role = Role::where('name', $this->selectedRole)->firstOrFail();
        $role->syncPermissions($this->rolePermissions);

        session()->flash('success', "Permissions du rôle «{$this->selectedRole}» mises à jour.");
    }

    public function openCreateRole(): void
    {
        $this->authorize('manage-roles');
        $this->newRoleName    = '';
        $this->showCreateModal = true;
    }

    public function createRole(): void
    {
        $this->authorize('manage-roles');
        $this->validate(['newRoleName' => 'required|string|max:50|unique:roles,name']);

        Role::create(['name' => $this->newRoleName]);
        session()->flash('success', "Rôle «{$this->newRoleName}» créé.");

        $this->showCreateModal = false;
        $this->newRoleName = '';
    }

    public function deleteRole(string $roleName): void
    {
        $this->authorize('manage-roles');

        if ($roleName === self::PROTECTED_ROLE) {
            session()->flash('error', 'Le rôle Administrateur ne peut pas être supprimé.');
            return;
        }

        $role = Role::where('name', $roleName)->firstOrFail();

        if ($role->users()->count() > 0) {
            session()->flash('error', "Impossible : {$role->users()->count()} utilisateur(s) ont ce rôle.");
            return;
        }

        $role->delete();

        if ($this->selectedRole === $roleName) {
            $this->selectedRole = null;
            $this->rolePermissions = [];
        }

        session()->flash('success', "Rôle «{$roleName}» supprimé.");
    }

    public function render()
    {
        $roles = Role::withCount('users')
            ->with('permissions')
            ->orderBy('name')
            ->get();

        $allPermissions = Permission::orderBy('name')->pluck('name')->toArray();
        $groups         = self::PERMISSION_GROUPS;

        return view('users::livewire.role-manager',
            compact('roles', 'allPermissions', 'groups'));
    }
}
