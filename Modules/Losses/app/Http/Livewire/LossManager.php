<?php

namespace Modules\Losses\app\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Losses\app\Enums\LossType;
use Modules\Losses\app\Models\Loss;
use Modules\Products\app\Models\Product;
use Modules\Stock\app\Services\StockService;

class LossManager extends Component
{
    use WithPagination;

    // Filtres
    public string $search     = '';
    public string $filterType = '';
    public string $dateFrom   = '';
    public string $dateTo     = '';

    // Formulaire
    public bool   $showModal    = false;
    #[Locked]
    public ?int   $editingId    = null;
    public string $formType     = 'loss';
    public ?int   $formProduct  = null;
    public float  $formQty      = 1;
    public ?float $formUnitCost = null;
    public string $formReason   = '';
    public string $formNotes    = '';

    public function updatedSearch(): void    { $this->resetPage(); }
    public function updatedFilterType(): void { $this->resetPage(); }
    public function updatedDateFrom(): void   { $this->resetPage(); }
    public function updatedDateTo(): void     { $this->resetPage(); }

    public function updatedFormProduct(): void
    {
        if ($this->formProduct) {
            $product = Product::find($this->formProduct);
            $this->formUnitCost = $product?->purchase_price;
        }
    }

    public function openCreate(): void
    {
        $this->authorize('create-losses');
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $this->authorize('edit-losses');
        $loss = Loss::findOrFail($id);

        $this->editingId    = $id;
        $this->formType     = $loss->type->value;
        $this->formProduct  = $loss->product_id;
        $this->formQty      = $loss->quantity;
        $this->formUnitCost = $loss->unit_cost;
        $this->formReason   = $loss->reason ?? '';
        $this->formNotes    = $loss->notes ?? '';
        $this->showModal    = true;
    }

    public function save(StockService $stock): void
    {
        $this->validate([
            'formType'     => 'required|in:loss,break,gift',
            'formProduct'  => 'required|integer|exists:products,id',
            'formQty'      => 'required|numeric|min:0.0001',
            'formUnitCost' => 'nullable|numeric|min:0',
            'formReason'   => 'nullable|string|max:500',
            'formNotes'    => 'nullable|string|max:500',
        ]);

        $type = LossType::from($this->formType);

        if ($this->editingId) {
            $this->authorize('edit-losses');
            $loss = Loss::findOrFail($this->editingId);
            $loss->update([
                'type'       => $type,
                'product_id' => $this->formProduct,
                'quantity'   => $this->formQty,
                'unit_cost'  => $this->formUnitCost,
                'reason'     => $this->formReason ?: null,
                'notes'      => $this->formNotes ?: null,
            ]);
            session()->flash('success', 'Déclaration mise à jour.');
        } else {
            $this->authorize('create-losses');

            $movement = $stock->deductStock(
                productId: $this->formProduct,
                quantity:  $this->formQty,
                type:      $type->movementType(),
                unitCost:  $this->formUnitCost,
                notes:     $type->label() . ($this->formReason ? " : {$this->formReason}" : ''),
            );

            Loss::create([
                'type'             => $type,
                'product_id'       => $this->formProduct,
                'quantity'         => $this->formQty,
                'unit_cost'        => $this->formUnitCost,
                'reason'           => $this->formReason ?: null,
                'notes'            => $this->formNotes ?: null,
                'declared_by'      => Auth::id(),
                'stock_movement_id'=> $movement->id,
            ]);

            session()->flash('success', 'Déclaration enregistrée, stock déduit.');
        }

        $this->showModal = false;
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $this->authorize('delete-losses');
        Loss::findOrFail($id)->delete();
        session()->flash('success', 'Déclaration supprimée (stock non réajusté — utiliser mouvement manuel si nécessaire).');
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'formProduct', 'formReason', 'formNotes', 'formUnitCost']);
        $this->formType = 'loss';
        $this->formQty  = 1;
    }

    public function render()
    {
        $losses = Loss::with('product', 'declaredBy')
            ->when($this->search, fn($q) => $q->whereHas(
                'product', fn($q) => $q->where('name', 'like', "%{$this->search}%")
            ))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->dateFrom,   fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,     fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderByDesc('created_at')
            ->paginate(25);

        $summary = Loss::selectRaw('type, SUM(total_cost) as total, SUM(quantity) as qty, COUNT(*) as cnt')
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $products = Product::active()->orderBy('name')->get();
        $types    = LossType::options();

        return view('losses::livewire.loss-manager',
            compact('losses', 'summary', 'products', 'types'));
    }
}
