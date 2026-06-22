<?php

namespace Modules\Products\app\Http\Livewire;

use Livewire\Attributes\Locked;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Modules\Categories\app\Models\Category;
use Modules\Products\app\Enums\ProductUnit;
use Modules\Products\app\Models\Product;

class ProductList extends Component
{
    use WithPagination, WithFileUploads;

    // Filters
    public string $search         = '';
    public string $filterCategory = '';
    public string $filterStatus   = '';

    // Modal state
    public bool $showModal  = false;
    public bool $showDelete = false;
    #[Locked]
    public ?int $editingId  = null;
    #[Locked]
    public ?int $deletingId = null;

    // Form fields
    #[Rule('required|string|max:50|unique:products,code')]
    public string $code = '';

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('nullable|integer|exists:categories,id')]
    public ?int $category_id = null;

    #[Rule('required|numeric|min:0')]
    public float $purchase_price = 0;

    #[Rule('required|numeric|min:0')]
    public float $selling_price = 0;

    #[Rule('required|integer|min:0')]
    public int $min_stock = 0;

    #[Rule('required|in:bouteille,verre,canette,carton,unité')]
    public string $unit = 'unité';

    #[Rule('nullable|string|max:500')]
    public string $notes = '';

    #[Rule('boolean')]
    public bool $is_active = true;

    public $image = null;

    // Live margin preview
    public float $previewMargin     = 0;
    public float $previewMarginRate = 0;
    public float $previewMarkupRate = 0;

    public function updatedPurchasePrice(): void { $this->computePreview(); }
    public function updatedSellingPrice(): void  { $this->computePreview(); }

    private function computePreview(): void
    {
        $buy  = (float) $this->purchase_price;
        $sell = (float) $this->selling_price;

        $this->previewMargin     = $sell - $buy;
        $this->previewMarginRate = $buy > 0  ? round((($sell - $buy) / $buy)  * 100, 2) : 0;
        $this->previewMarkupRate = $sell > 0 ? round((($sell - $buy) / $sell) * 100, 2) : 0;
    }

    public function updatedSearch(): void    { $this->resetPage(); }
    public function updatedFilterCategory(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void   { $this->resetPage(); }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function openCreate(): void
    {
        $this->authorize('create-products');
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $this->authorize('edit-products');
        $p = Product::findOrFail($id);

        $this->editingId      = $id;
        $this->code           = $p->code;
        $this->name           = $p->name;
        $this->category_id    = $p->category_id;
        $this->purchase_price = $p->purchase_price;
        $this->selling_price  = $p->selling_price;
        $this->min_stock      = $p->min_stock;
        $this->unit           = $p->unit->value;
        $this->notes          = $p->notes ?? '';
        $this->is_active      = $p->is_active;
        $this->image          = null;
        $this->computePreview();
        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = $this->getRules();

        // unique:products,code ignore en édition
        if ($this->editingId) {
            $rules['code'] = "required|string|max:50|unique:products,code,{$this->editingId}";
        }

        $this->validate($rules);

        $data = [
            'code'           => strtoupper($this->code),
            'name'           => $this->name,
            'category_id'    => $this->category_id ?: null,
            'purchase_price' => $this->purchase_price,
            'selling_price'  => $this->selling_price,
            'min_stock'      => $this->min_stock,
            'unit'           => $this->unit,
            'notes'          => $this->notes ?: null,
            'is_active'      => $this->is_active,
        ];

        if ($this->image) {
            $this->validate(['image' => 'image|max:2048']);
            $data['image'] = $this->image->store('products', 'public');
        }

        if ($this->editingId) {
            $this->authorize('edit-products');
            Product::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Produit mis à jour.');
        } else {
            $this->authorize('create-products');
            Product::create($data);
            session()->flash('success', 'Produit créé.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->authorize('delete-products');
        $this->deletingId = $id;
        $this->showDelete = true;
    }

    public function delete(): void
    {
        $this->authorize('delete-products');
        $p = Product::findOrFail($this->deletingId);

        if ($p->stock_quantity > 0) {
            session()->flash('error', "Impossible : stock non nul ({$p->stock_quantity}). Faites un ajustement de stock d'abord.");
            $this->showDelete = false;
            return;
        }

        $p->delete();
        $this->showDelete = false;
        session()->flash('success', 'Produit supprimé.');
    }

    public function toggleActive(int $id): void
    {
        $this->authorize('edit-products');
        $p = Product::findOrFail($id);
        $p->update(['is_active' => ! $p->is_active]);
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'code', 'name', 'category_id', 'purchase_price',
            'selling_price', 'min_stock', 'notes', 'image',
            'previewMargin', 'previewMarginRate', 'previewMarkupRate',
        ]);
        $this->unit      = 'unité';
        $this->is_active = true;
    }

    public function render()
    {
        $products = Product::with('category')
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%");
            }))
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterStatus === 'active',   fn($q) => $q->where('is_active', true))
            ->when($this->filterStatus === 'inactive', fn($q) => $q->where('is_active', false))
            ->when($this->filterStatus === 'low',      fn($q) => $q->whereColumn('stock_quantity', '<=', 'min_stock'))
            ->orderBy('name')
            ->paginate(25);

        $categories    = Category::active()->ordered()->get();
        $units         = ProductUnit::options();
        $lowStockCount = Product::active()
            ->where('min_stock', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->count();
        $totalActive = Product::where('is_active', true)->count();

        return view('products::livewire.product-list',
            compact('products', 'categories', 'units', 'lowStockCount', 'totalActive'));
    }
}
