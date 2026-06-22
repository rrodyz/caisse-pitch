<?php

namespace Modules\Inventories\app\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Inventories\app\Enums\InventoryStatus;
use Modules\Inventories\app\Models\Inventory;
use Modules\Inventories\app\Models\InventoryItem;
use Modules\Products\app\Models\Product;
use Modules\Stock\app\Services\StockService;

class InventoryManager extends Component
{
    use WithPagination;

    public string $view         = 'list';
    #[Locked]
    public ?int   $inventoryId  = null;
    public string $searchList   = '';
    public string $searchItems  = '';
    public string $filterStatus = '';
    public string $notes        = '';

    // Items en cours de saisie : [product_id => counted_quantity]
    public array $counts = [];

    public function updatedSearchList(): void   { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    // ── Créer un inventaire ──────────────────────────────────────────────────

    public function createInventory(): void
    {
        $this->authorize('manage-inventory');

        DB::transaction(function () {
            $inventory = Inventory::create([
                'reference'  => Inventory::generateReference(),
                'status'     => InventoryStatus::InProgress,
                'notes'      => $this->notes ?: null,
                'started_by' => Auth::id(),
                'started_at' => now(),
            ]);

            // Snapshot tous produits actifs
            Product::active()->orderBy('name')->chunk(200, function ($products) use ($inventory) {
                foreach ($products as $product) {
                    InventoryItem::create([
                        'inventory_id'         => $inventory->id,
                        'product_id'           => $product->id,
                        'theoretical_quantity' => $product->stock_quantity,
                        'counted_quantity'     => null,
                        'unit_cost'            => $product->purchase_price,
                    ]);
                }
            });

            $this->inventoryId = $inventory->id;
        });

        $this->notes  = '';
        $this->counts = [];
        $this->view   = 'detail';
        session()->flash('success', 'Inventaire démarré.');
    }

    // ── Ouvrir un inventaire existant ────────────────────────────────────────

    public function openInventory(int $id): void
    {
        $this->authorize('manage-inventory');
        $inventory = Inventory::findOrFail($id);

        if (! $inventory->status->isEditable()) {
            session()->flash('error', 'Cet inventaire ne peut plus être modifié.');
            return;
        }

        $this->inventoryId = $id;
        $this->counts      = [];
        $this->searchItems = '';
        $this->view        = 'detail';
    }

    // ── Sauvegarder les comptages partiels ──────────────────────────────────

    public function saveCounts(): void
    {
        $this->authorize('manage-inventory');
        $inventory = Inventory::findOrFail($this->inventoryId);

        if (! $inventory->status->isEditable()) {
            $this->addError('counts', 'Inventaire non modifiable.');
            return;
        }

        foreach ($this->counts as $productId => $qty) {
            if ($qty === '' || $qty === null) {
                continue;
            }
            $item = InventoryItem::where('inventory_id', $this->inventoryId)
                ->where('product_id', $productId)
                ->first();

            if ($item) {
                $item->update(['counted_quantity' => (float) $qty]);
            }
        }

        $this->counts = [];
        session()->flash('success', 'Comptages sauvegardés.');
    }

    // ── Valider l'inventaire ─────────────────────────────────────────────────

    public function validateInventory(StockService $stock): void
    {
        $this->authorize('manage-inventory');

        DB::transaction(function () use ($stock) {
            $inventory = Inventory::with('items.product')->findOrFail($this->inventoryId);

            if (! $inventory->status->isEditable()) {
                $this->addError('counts', 'Cet inventaire ne peut pas être validé.');
                return;
            }

            // Sauvegarder les comptages en cours avant validation
            foreach ($this->counts as $productId => $qty) {
                if ($qty === '' || $qty === null) continue;
                InventoryItem::where('inventory_id', $this->inventoryId)
                    ->where('product_id', $productId)
                    ->update(['counted_quantity' => (float) $qty]);
            }

            // Ajuster le stock pour chaque écart
            foreach ($inventory->items as $item) {
                if ($item->counted_quantity === null) {
                    continue; // non compté = ignoré
                }
                if (abs($item->gap) > 0.0001) {
                    $stock->adjustStock(
                        productId:   $item->product_id,
                        newQuantity: $item->counted_quantity,
                        notes:       "Inventaire {$inventory->reference}",
                    );
                }
            }

            $inventory->update([
                'status'       => InventoryStatus::Validated,
                'validated_by' => Auth::id(),
                'validated_at' => now(),
            ]);

            $this->counts = [];
        });

        session()->flash('success', 'Inventaire validé, stock réconcilié.');
        $this->view = 'list';
        $this->inventoryId = null;
    }

    public function cancelInventory(int $id): void
    {
        $this->authorize('manage-inventory');
        $inventory = Inventory::findOrFail($id);

        if (! $inventory->status->isEditable()) {
            session()->flash('error', 'Cet inventaire ne peut pas être annulé.');
            return;
        }

        $inventory->update(['status' => InventoryStatus::Cancelled]);
        session()->flash('success', 'Inventaire annulé.');

        if ($this->inventoryId === $id) {
            $this->view = 'list';
            $this->inventoryId = null;
        }
    }

    public function backToList(): void
    {
        $this->view        = 'list';
        $this->inventoryId = null;
        $this->counts      = [];
    }

    // ── Rendu ────────────────────────────────────────────────────────────────

    public function render()
    {
        if ($this->view === 'detail' && $this->inventoryId) {
            $inventory = Inventory::with('startedBy', 'validatedBy')->findOrFail($this->inventoryId);

            $itemsQuery = InventoryItem::with('product')
                ->where('inventory_id', $this->inventoryId)
                ->when($this->searchItems, fn($q) => $q->whereHas(
                    'product', fn($q) => $q->where('name', 'like', "%{$this->searchItems}%")
                ))
                ->orderBy('product_id');

            $items = $itemsQuery->paginate(50);

            $totalGapCost = InventoryItem::where('inventory_id', $this->inventoryId)
                ->sum('gap_cost');
            $countedCount = InventoryItem::where('inventory_id', $this->inventoryId)
                ->whereNotNull('counted_quantity')->count();
            $totalCount   = InventoryItem::where('inventory_id', $this->inventoryId)->count();

            return view('inventories::livewire.inventory-detail',
                compact('inventory', 'items', 'totalGapCost', 'countedCount', 'totalCount'));
        }

        $inventories = Inventory::withCount('items')
            ->with('startedBy', 'validatedBy')
            ->when($this->searchList, fn($q) => $q->where('reference', 'like', "%{$this->searchList}%"))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderByDesc('created_at')
            ->paginate(20);

        $statuses = InventoryStatus::cases();

        return view('inventories::livewire.inventory-list',
            compact('inventories', 'statuses'));
    }
}
