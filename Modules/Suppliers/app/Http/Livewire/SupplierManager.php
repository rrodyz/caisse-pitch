<?php

namespace Modules\Suppliers\app\Http\Livewire;

use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Suppliers\app\Models\Supplier;

class SupplierManager extends Component
{
    use WithPagination;

    public bool $showModal  = false;
    public ?int $editingId  = null;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('nullable|string|max:20')]
    public string $phone = '';

    #[Rule('nullable|email|max:255')]
    public string $email = '';

    #[Rule('nullable|string|max:500')]
    public string $address = '';

    #[Rule('nullable|string|max:50')]
    public string $ifu = '';

    #[Rule('nullable|string|max:100')]
    public string $contact_name = '';

    #[Rule('nullable|string|max:500')]
    public string $notes = '';

    public bool $is_active = true;
    public string $search  = '';

    public function updatedSearch(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->authorize('create-suppliers');
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $this->authorize('edit-suppliers');
        $s = Supplier::findOrFail($id);

        $this->editingId    = $id;
        $this->name         = $s->name;
        $this->phone        = $s->phone ?? '';
        $this->email        = $s->email ?? '';
        $this->address      = $s->address ?? '';
        $this->ifu          = $s->ifu ?? '';
        $this->contact_name = $s->contact_name ?? '';
        $this->notes        = $s->notes ?? '';
        $this->is_active    = $s->is_active;
        $this->showModal    = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'         => $this->name,
            'phone'        => $this->phone ?: null,
            'email'        => $this->email ?: null,
            'address'      => $this->address ?: null,
            'ifu'          => $this->ifu ?: null,
            'contact_name' => $this->contact_name ?: null,
            'notes'        => $this->notes ?: null,
            'is_active'    => $this->is_active,
        ];

        if ($this->editingId) {
            $this->authorize('edit-suppliers');
            Supplier::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Fournisseur mis à jour.');
        } else {
            $this->authorize('create-suppliers');
            Supplier::create($data);
            session()->flash('success', 'Fournisseur créé.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $this->authorize('edit-suppliers');
        $s = Supplier::findOrFail($id);
        $s->update(['is_active' => ! $s->is_active]);
    }

    public function delete(int $id): void
    {
        $this->authorize('delete-suppliers');
        $s = Supplier::withCount('purchases')->findOrFail($id);

        if ($s->purchases_count > 0) {
            session()->flash('error', "Impossible : {$s->purchases_count} achat(s) lié(s) à ce fournisseur.");
            return;
        }

        $s->delete();
        session()->flash('success', 'Fournisseur supprimé.');
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'phone', 'email', 'address', 'ifu', 'contact_name', 'notes']);
        $this->is_active = true;
    }

    public function render()
    {
        $suppliers = Supplier::query()
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%")
                  ->orWhere('ifu', 'like', "%{$this->search}%");
            }))
            ->withCount('purchases')
            ->orderBy('name')
            ->paginate(20);

        return view('suppliers::livewire.supplier-manager', compact('suppliers'));
    }
}
