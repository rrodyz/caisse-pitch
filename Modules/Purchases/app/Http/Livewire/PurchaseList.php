<?php

namespace Modules\Purchases\app\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Purchases\app\Models\Purchase;
use Modules\Suppliers\app\Models\Supplier;

class PurchaseList extends Component
{
    use WithPagination;

    public string $search         = '';
    public string $filterStatus   = '';
    public string $filterSupplier = '';
    public string $filterFrom     = '';
    public string $filterTo       = '';

    public function updatedSearch(): void        { $this->resetPage(); }
    public function updatedFilterStatus(): void  { $this->resetPage(); }
    public function updatedFilterSupplier(): void{ $this->resetPage(); }

    public function validatePurchase(int $id): void
    {
        $this->authorize('validate-purchases');
        $purchase = Purchase::findOrFail($id);

        try {
            $purchase->validate(auth()->id());
            session()->flash('success', "Achat {$purchase->number} validé. Stock mis à jour.");
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function cancelPurchase(int $id): void
    {
        $this->authorize('validate-purchases');
        Purchase::findOrFail($id)->cancel();
        session()->flash('success', 'Achat annulé.');
    }

    public function render()
    {
        $purchases = Purchase::with(['supplier', 'creator'])
            ->when($this->search, fn($q) => $q->where('number', 'like', "%{$this->search}%"))
            ->when($this->filterStatus,   fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterSupplier, fn($q) => $q->where('supplier_id', $this->filterSupplier))
            ->when($this->filterFrom, fn($q) => $q->whereDate('date', '>=', $this->filterFrom))
            ->when($this->filterTo,   fn($q) => $q->whereDate('date', '<=', $this->filterTo))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(25);

        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('purchases::livewire.purchase-list', compact('purchases', 'suppliers'));
    }
}
