<?php

namespace Modules\Stock\app\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Products\app\Models\Product;
use Modules\Stock\app\Enums\MovementType;
use Modules\Stock\app\Models\StockMovement;
use Modules\Stock\app\Services\StockService;

class StockMovements extends Component
{
    use WithPagination;

    // Onglets
    public string $tab         = 'stock';

    // Filtres liste
    public string $search      = '';
    public string $filterType  = '';
    public string $dateFrom    = '';
    public string $dateTo      = '';
    public string $stockSearch = '';
    public string $stockFilter = ''; // '' | 'ok' | 'low' | 'empty'

    // Formulaire entrée/sortie manuelle
    public bool   $showForm    = false;
    public ?int   $formProduct = null;
    public string $formType    = 'manual_in';
    public float  $formQty     = 1;
    public string $formNotes   = '';

    public function updatedSearch(): void   { $this->resetPage(); }
    public function updatedFilterType(): void { $this->resetPage(); }
    public function updatedDateFrom(): void   { $this->resetPage(); }
    public function updatedDateTo(): void     { $this->resetPage(); }

    public function openForm(): void
    {
        $this->authorize('adjust-stock');
        $this->reset(['formProduct', 'formQty', 'formNotes']);
        $this->formType = 'manual_in';
        $this->formQty  = 1;
        $this->showForm = true;
    }

    public function quickAdjust(int $productId, string $type): void
    {
        $this->authorize('adjust-stock');
        $this->formProduct = $productId;
        $this->formType    = $type;
        $this->formQty     = 1;
        $this->formNotes   = '';
        $this->showForm    = true;
        $this->tab         = 'journal'; // bascule sur le journal pour voir le formulaire
    }

    public function saveMovement(StockService $stock): void
    {
        $this->authorize('adjust-stock');

        $this->validate([
            'formProduct' => 'required|integer|exists:products,id',
            'formType'    => 'required|in:manual_in,manual_out',
            'formQty'     => 'required|numeric|min:0.0001',
        ]);

        $type = MovementType::from($this->formType);

        if ($type === MovementType::ManualIn) {
            $stock->addStock($this->formProduct, $this->formQty, $type, notes: $this->formNotes);
        } else {
            $stock->deductStock($this->formProduct, $this->formQty, $type, notes: $this->formNotes);
        }

        session()->flash('success', 'Mouvement enregistré.');
        $this->showForm = false;
        $this->resetPage();
    }

    public function render()
    {
        $movements = StockMovement::with('product', 'user')
            ->when($this->search, fn($q) => $q->whereHas(
                'product', fn($q) => $q->where('name', 'like', "%{$this->search}%")
            ))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->dateFrom,   fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,     fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderByDesc('created_at')
            ->paginate(30);

        $products = Product::active()->orderBy('name')->get();
        $types    = MovementType::options();
        $lowStock = app(StockService::class)->getLowStockProducts();

        // Onglet État du stock
        $stockProducts = Product::active()
            ->with('category:id,name,color')
            ->when($this->stockSearch, fn($q) => $q->where('name', 'like', "%{$this->stockSearch}%"))
            ->when($this->stockFilter === 'empty', fn($q) => $q->where('stock_quantity', '<=', 0))
            ->when($this->stockFilter === 'low',   fn($q) => $q->where('min_stock', '>', 0)->whereColumn('stock_quantity', '<=', 'min_stock')->where('stock_quantity', '>', 0))
            ->when($this->stockFilter === 'ok',    fn($q) => $q->where(fn($q) => $q->where('min_stock', 0)->orWhereColumn('stock_quantity', '>', 'min_stock')))
            ->orderBy('name')
            ->get();

        return view('stock::livewire.stock-movements',
            compact('movements', 'products', 'types', 'lowStock', 'stockProducts'));
    }
}
