<?php

namespace Modules\Categories\app\Http\Livewire;

use Livewire\Attributes\Rule;
use Livewire\Component;
use Modules\Categories\app\Models\Category;

class CategoryManager extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    #[Rule('required|string|max:100')]
    public string $name = '';

    #[Rule('nullable|string|max:255')]
    public string $description = '';

    #[Rule('required|regex:/^#[0-9A-Fa-f]{6}$/')]
    public string $color = '#6366f1';

    #[Rule('required|integer|min:0')]
    public int $pos_order = 0;

    #[Rule('boolean')]
    public bool $is_active = true;

    public string $search = '';

    public function openCreate(): void
    {
        $this->authorize('create-categories');
        $this->reset(['name', 'description', 'color', 'pos_order', 'is_active', 'editingId']);
        $this->color     = '#6366f1';
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $this->authorize('edit-categories');
        $cat = Category::findOrFail($id);

        $this->editingId   = $id;
        $this->name        = $cat->name;
        $this->description = $cat->description ?? '';
        $this->color       = $cat->color;
        $this->pos_order   = $cat->pos_order;
        $this->is_active   = $cat->is_active;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();
        $data = [
            'name'        => $this->name,
            'description' => $this->description ?: null,
            'color'       => $this->color,
            'pos_order'   => $this->pos_order,
            'is_active'   => $this->is_active,
        ];

        if ($this->editingId) {
            $this->authorize('edit-categories');
            Category::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Catégorie mise à jour.');
        } else {
            $this->authorize('create-categories');
            Category::create($data);
            session()->flash('success', 'Catégorie créée.');
        }

        $this->showModal = false;
        $this->reset(['name', 'description', 'color', 'pos_order', 'editingId']);
    }

    public function toggleActive(int $id): void
    {
        $this->authorize('edit-categories');
        $cat = Category::findOrFail($id);
        $cat->update(['is_active' => ! $cat->is_active]);
    }

    public function delete(int $id): void
    {
        $this->authorize('delete-categories');
        $cat = Category::findOrFail($id);

        if ($cat->products()->count() > 0) {
            session()->flash('error', 'Impossible de supprimer : des produits utilisent cette catégorie.');
            return;
        }

        $cat->delete();
        session()->flash('success', 'Catégorie supprimée.');
    }

    public function render()
    {
        $categories = Category::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('pos_order')
            ->orderBy('name')
            ->withCount('products')
            ->paginate(20);

        return view('categories::livewire.category-manager', compact('categories'));
    }
}
