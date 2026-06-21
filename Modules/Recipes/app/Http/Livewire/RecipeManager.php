<?php

namespace Modules\Recipes\app\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Products\app\Models\Product;
use Modules\Recipes\app\Models\Recipe;
use Modules\Recipes\app\Models\RecipeIngredient;

class RecipeManager extends Component
{
    use WithPagination;

    // Vue courante : list | form
    public string $view      = 'list';
    public ?int   $editingId = null;

    // Champs du formulaire
    public ?int   $product_id  = null;
    public string $description = '';
    public bool   $is_active   = true;

    // Ingrédients dynamiques
    public array $ingredients = [];

    // Preview coût calculé côté UI
    public float $previewCost       = 0;
    public float $previewMargin     = 0;
    public float $previewMarginRate = 0;
    public float $previewMarkupRate = 0;

    public string $search = '';

    public function updatedSearch(): void { $this->resetPage(); }

    // ── Navigation ──────────────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->authorize('create-recipes');
        $this->resetForm();
        $this->view = 'form';
    }

    public function openEdit(int $id): void
    {
        $this->authorize('edit-recipes');
        $recipe = Recipe::with('ingredients.product')->findOrFail($id);

        $this->editingId   = $id;
        $this->product_id  = $recipe->product_id;
        $this->description = $recipe->description ?? '';
        $this->is_active   = $recipe->is_active;

        $this->ingredients = $recipe->ingredients->map(fn($i) => [
            'product_id' => $i->product_id,
            'quantity'   => $i->quantity,
            'cost'       => round(($i->product?->purchase_price ?? 0) * $i->quantity, 2),
        ])->toArray();

        if (empty($this->ingredients)) {
            $this->addIngredient();
        }

        $this->computePreview();
        $this->view = 'form';
    }

    public function backToList(): void
    {
        $this->view = 'list';
        $this->resetForm();
    }

    // ── Ingrédients dynamiques ───────────────────────────────────────────────

    public function addIngredient(): void
    {
        $this->ingredients[] = ['product_id' => null, 'quantity' => 1, 'cost' => 0];
    }

    public function removeIngredient(int $index): void
    {
        unset($this->ingredients[$index]);
        $this->ingredients = array_values($this->ingredients);
        $this->computePreview();
    }

    public function updatedIngredients($value, $key): void
    {
        [$index, $field] = explode('.', $key, 2);
        $index = (int) $index;

        if ($field === 'product_id' && $value) {
            $product = Product::find($value);
            $qty     = (float) ($this->ingredients[$index]['quantity'] ?? 1);
            $this->ingredients[$index]['cost'] = round(($product?->purchase_price ?? 0) * $qty, 2);
        }

        if (in_array($field, ['product_id', 'quantity'])) {
            $pid = (int) ($this->ingredients[$index]['product_id'] ?? 0);
            $qty = (float) ($this->ingredients[$index]['quantity'] ?? 0);
            if ($pid) {
                $product = Product::find($pid);
                $this->ingredients[$index]['cost'] = round(($product?->purchase_price ?? 0) * $qty, 2);
            }
        }

        $this->computePreview();
    }

    public function updatedProductId(): void
    {
        $this->computePreview();
    }

    private function computePreview(): void
    {
        $this->previewCost = collect($this->ingredients)->sum('cost');

        $sell = 0;
        if ($this->product_id) {
            $sell = (float) (Product::find($this->product_id)?->selling_price ?? 0);
        }

        $margin = $sell - $this->previewCost;
        $this->previewMargin     = $margin;
        $this->previewMarginRate = $this->previewCost > 0 ? round(($margin / $this->previewCost) * 100, 2) : 0;
        $this->previewMarkupRate = $sell > 0             ? round(($margin / $sell) * 100, 2)             : 0;
    }

    // ── Sauvegarde ───────────────────────────────────────────────────────────

    public function save(): void
    {
        $this->validate([
            'product_id'               => 'required|integer|exists:products,id',
            'description'              => 'nullable|string|max:500',
            'ingredients'              => 'required|array|min:1',
            'ingredients.*.product_id' => 'required|integer|exists:products,id',
            'ingredients.*.quantity'   => 'required|numeric|min:0.0001',
        ]);

        // Un produit ne peut pas être ingrédient de lui-même
        foreach ($this->ingredients as $i) {
            if ((int) $i['product_id'] === (int) $this->product_id) {
                $this->addError('ingredients', 'Un produit ne peut pas être son propre ingrédient.');
                return;
            }
        }

        // Unique: vérifier qu'un autre produit n'a pas déjà cette recette
        $query = Recipe::where('product_id', $this->product_id);
        if ($this->editingId) {
            $query->where('id', '!=', $this->editingId);
        }
        if ($query->exists()) {
            $this->addError('product_id', 'Ce produit a déjà une recette.');
            return;
        }

        if ($this->editingId) {
            $this->authorize('edit-recipes');
            $recipe = Recipe::findOrFail($this->editingId);
            $recipe->update([
                'product_id'  => $this->product_id,
                'description' => $this->description ?: null,
                'is_active'   => $this->is_active,
            ]);
            $recipe->ingredients()->delete();
        } else {
            $this->authorize('create-recipes');
            $recipe = Recipe::create([
                'product_id'  => $this->product_id,
                'description' => $this->description ?: null,
                'is_active'   => $this->is_active,
                'cost_price'  => 0,
            ]);
        }

        foreach ($this->ingredients as $i) {
            RecipeIngredient::create([
                'recipe_id'  => $recipe->id,
                'product_id' => $i['product_id'],
                'quantity'   => $i['quantity'],
            ]);
        }

        $recipe->recalculate();

        session()->flash('success', $this->editingId ? 'Recette mise à jour.' : 'Recette créée.');
        $this->backToList();
    }

    public function delete(int $id): void
    {
        $this->authorize('delete-recipes');
        Recipe::findOrFail($id)->delete();
        session()->flash('success', 'Recette supprimée.');
    }

    public function toggleActive(int $id): void
    {
        $this->authorize('edit-recipes');
        $r = Recipe::findOrFail($id);
        $r->update(['is_active' => ! $r->is_active]);
    }

    // ── Reset ────────────────────────────────────────────────────────────────

    private function resetForm(): void
    {
        $this->reset(['editingId', 'product_id', 'description', 'ingredients',
                      'previewCost', 'previewMargin', 'previewMarginRate', 'previewMarkupRate']);
        $this->is_active   = true;
        $this->ingredients = [['product_id' => null, 'quantity' => 1, 'cost' => 0]];
    }

    // ── Rendu ────────────────────────────────────────────────────────────────

    public function render()
    {
        $recipes = Recipe::with('product.category')
            ->withCount('ingredients')
            ->when($this->search, fn($q) => $q->whereHas(
                'product', fn($q) => $q->where('name', 'like', "%{$this->search}%")
            ))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Produits sans recette déjà assignée (sauf celui en cours d'édition)
        $existingProductIds = Recipe::when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
            ->pluck('product_id');

        $compositeProducts = Product::active()
            ->when($this->product_id, fn($q) => $q->orWhere('id', $this->product_id))
            ->whereNotIn('id', $existingProductIds)
            ->orderBy('name')
            ->get();

        $allProducts = Product::active()->orderBy('name')->get();

        return view('recipes::livewire.recipe-manager', compact('recipes', 'compositeProducts', 'allProducts'));
    }
}
