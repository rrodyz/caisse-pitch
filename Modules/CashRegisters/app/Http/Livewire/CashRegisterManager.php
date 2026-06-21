<?php

namespace Modules\CashRegisters\app\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\CashRegisters\app\Models\CashRegister;

class CashRegisterManager extends Component
{
    use WithPagination;

    public bool   $showModal = false;
    public ?int   $editingId = null;
    public string $name      = '';
    public string $location  = '';
    public bool   $is_active = true;

    public function openCreate(): void
    {
        $this->authorize('manage-cash-registers');
        $this->reset(['editingId', 'name', 'location']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $this->authorize('manage-cash-registers');
        $r = CashRegister::findOrFail($id);
        $this->editingId = $id;
        $this->name      = $r->name;
        $this->location  = $r->location ?? '';
        $this->is_active = $r->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->authorize('manage-cash-registers');
        $this->validate([
            'name'     => 'required|string|max:100',
            'location' => 'nullable|string|max:150',
        ]);

        if ($this->editingId) {
            CashRegister::findOrFail($this->editingId)->update([
                'name'      => $this->name,
                'location'  => $this->location ?: null,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', 'Caisse mise à jour.');
        } else {
            CashRegister::create([
                'name'      => $this->name,
                'location'  => $this->location ?: null,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', 'Caisse créée.');
        }

        $this->showModal = false;
        $this->resetPage();
    }

    public function toggleActive(int $id): void
    {
        $this->authorize('manage-cash-registers');
        $r = CashRegister::findOrFail($id);
        $r->update(['is_active' => ! $r->is_active]);
    }

    public function render()
    {
        $registers = CashRegister::withCount('sessions')
            ->with('activeSession.openedBy')
            ->orderBy('name')
            ->paginate(20);

        return view('cashregisters::livewire.cash-register-manager', compact('registers'));
    }
}
