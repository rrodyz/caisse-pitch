<?php

namespace Modules\Users\app\Http\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserManager extends Component
{
    use WithPagination;

    public string $search      = '';
    public string $filterRole  = '';
    public string $filterStatus = '';

    public bool   $showModal  = false;
    public ?int   $editingId  = null;

    public string $first_name = '';
    public string $last_name  = '';
    public string $email      = '';
    public string $username   = '';
    public string $phone      = '';
    public string $password   = '';
    public bool   $is_active  = true;
    public string $role_name  = '';

    public function updatedSearch(): void      { $this->resetPage(); }
    public function updatedFilterRole(): void  { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->authorize('create-users');
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $this->authorize('edit-users');
        $user = User::with('roles')->findOrFail($id);

        $this->editingId  = $id;
        $this->first_name = $user->first_name;
        $this->last_name  = $user->last_name;
        $this->email      = $user->email;
        $this->username   = $user->username ?? '';
        $this->phone      = $user->phone ?? '';
        $this->password   = '';
        $this->is_active  = $user->is_active;
        $this->role_name  = $user->roles->first()?->name ?? '';
        $this->showModal  = true;
    }

    public function save(): void
    {
        $isCreate = ! $this->editingId;

        if ($isCreate) {
            $this->authorize('create-users');
        } else {
            $this->authorize('edit-users');
        }

        $rules = [
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|max:255|unique:users,email' . ($this->editingId ? ",{$this->editingId}" : ''),
            'username'   => 'required|string|max:50|alpha_dash|unique:users,username' . ($this->editingId ? ",{$this->editingId}" : ''),
            'phone'      => 'nullable|string|max:20',
            'role_name'  => 'required|string|exists:roles,name',
            'is_active'  => 'boolean',
        ];

        if ($isCreate) {
            $rules['password'] = 'required|string|min:8';
        } else {
            $rules['password'] = 'nullable|string|min:8';
        }

        $this->validate($rules);

        $data = [
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email,
            'username'   => $this->username,
            'phone'      => $this->phone ?: null,
            'is_active'  => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($isCreate) {
            $user = User::create($data);
            session()->flash('success', 'Utilisateur créé.');
        } else {
            $user = User::findOrFail($this->editingId);
            $user->update($data);
            session()->flash('success', 'Utilisateur mis à jour.');
        }

        $user->syncRoles([$this->role_name]);

        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();
    }

    public function toggleActive(int $id): void
    {
        $this->authorize('edit-users');

        if ($id === auth()->id()) {
            session()->flash('error', 'Vous ne pouvez pas désactiver votre propre compte.');
            return;
        }

        $user = User::findOrFail($id);
        $user->update(['is_active' => ! $user->is_active]);
    }

    public function delete(int $id): void
    {
        $this->authorize('delete-users');

        if ($id === auth()->id()) {
            session()->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return;
        }

        $user = User::findOrFail($id);

        $salesCount = \Illuminate\Support\Facades\DB::table('sales')
            ->where('served_by', $id)->count();

        if ($salesCount > 0) {
            session()->flash('error', "Impossible : {$salesCount} vente(s) liée(s) à cet utilisateur.");
            return;
        }

        $user->delete();
        session()->flash('success', 'Utilisateur supprimé.');
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'first_name', 'last_name', 'email',
            'username', 'phone', 'password', 'role_name',
        ]);
        $this->is_active = true;
    }

    public function render()
    {
        $users = User::with('roles')
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                  ->orWhere('last_name',  'like', "%{$this->search}%")
                  ->orWhere('email',      'like', "%{$this->search}%")
                  ->orWhere('username',   'like', "%{$this->search}%");
            }))
            ->when($this->filterRole, fn($q) => $q->whereHas(
                'roles', fn($q) => $q->where('name', $this->filterRole)
            ))
            ->when($this->filterStatus !== '', fn($q) => $q->where(
                'is_active', $this->filterStatus === '1'
            ))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(25);

        $roles = Role::orderBy('name')->get();

        return view('users::livewire.user-manager', compact('users', 'roles'));
    }
}
