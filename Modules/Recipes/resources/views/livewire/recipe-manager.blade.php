<div class="p-6">

{{-- ═══════ VUE LISTE ═══════ --}}
@if ($view === 'list')

    <div class="flex items-center justify-between mb-4 gap-3">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher un cocktail..."
            class="border-white/10 rounded-md shadow-sm text-sm w-64 focus:ring-neon-500/30 focus:border-neon-500">
        @can('create-recipes')
            <button wire:click="openCreate"
                class="inline-flex items-center px-4 py-2 bg-neon-600 text-white text-sm font-semibold rounded-lg hover:bg-neon-500">
                + Nouvelle recette
            </button>
        @endcan
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/5 text-sm">
            <thead class="bg-night-700">
                <tr>
                    <th class="px-3 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Produit composé</th>
                    <th class="px-3 py-3 text-center font-medium text-night-200 uppercase tracking-wider">Ingrédients</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200 uppercase tracking-wider">Coût revient</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200 uppercase tracking-wider">Prix vente</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200 uppercase tracking-wider">Marge</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200 uppercase tracking-wider">Tx marge</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Statut</th>
                    <th class="px-3 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse ($recipes as $recipe)
                    <tr wire:key="rec-{{ $recipe->id }}" class="{{ $recipe->is_active ? '' : 'opacity-50' }}">
                        <td class="px-3 py-3">
                            <div class="font-medium text-night-50">{{ $recipe->product?->name ?? '—' }}</div>
                            @if ($recipe->product?->category)
                                <span class="text-xs px-2 py-0.5 rounded-full text-white"
                                    style="background-color: {{ $recipe->product->category->color }}">
                                    {{ $recipe->product->category->name }}
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center text-night-200">{{ $recipe->ingredients_count }}</td>
                        <td class="px-3 py-3 text-right text-night-100">
                            {{ number_format($recipe->cost_price, 0, ',', ' ') }}
                        </td>
                        <td class="px-3 py-3 text-right text-night-50 font-medium">
                            {{ number_format($recipe->product?->selling_price ?? 0, 0, ',', ' ') }}
                        </td>
                        <td class="px-3 py-3 text-right font-semibold {{ $recipe->margin >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ number_format($recipe->margin, 0, ',', ' ') }}
                        </td>
                        <td class="px-3 py-3 text-right text-night-300 text-xs">
                            {{ number_format($recipe->margin_rate, 1) }}%
                            <div class="text-night-300">{{ number_format($recipe->markup_rate, 1) }}%*</div>
                        </td>
                        <td class="px-3 py-3">
                            <button wire:click="toggleActive({{ $recipe->id }})"
                                class="inline-flex items-center px-2 py-1 text-xs rounded-full font-medium
                                    {{ $recipe->is_active ? 'bg-emerald-500/15 text-emerald-300' : 'bg-night-700 text-night-200' }}">
                                {{ $recipe->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-3 py-3 text-right space-x-2 whitespace-nowrap">
                            @can('edit-recipes')
                                <button wire:click="openEdit({{ $recipe->id }})" class="text-neon-400 hover:text-neon-200 text-xs font-medium">Modifier</button>
                            @endcan
                            @can('delete-recipes')
                                <button wire:click="delete({{ $recipe->id }})"
                                    wire:confirm="Supprimer cette recette ?"
                                    class="text-red-400 hover:text-red-400 text-xs font-medium">Supprimer</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-night-300">Aucune recette.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-2 text-xs text-night-300">* Tx marge = (marge / coût)×100 &nbsp;|&nbsp; Tx marque = (marge / vente)×100</div>
    <div class="mt-3">{{ $recipes->links() }}</div>

{{-- ═══════ VUE FORMULAIRE ═══════ --}}
@elseif ($view === 'form')

    <div class="flex items-center gap-3 mb-6">
        <button wire:click="backToList" class="text-night-300 hover:text-night-200 text-sm">← Recettes</button>
        <h3 class="text-lg font-semibold text-night-50">{{ $editingId ? 'Modifier la recette' : 'Nouvelle recette' }}</h3>
    </div>

    @error('ingredients') <div class="mb-3 text-red-500 text-sm">{{ $message }}</div> @enderror

    <form wire:submit="save" class="space-y-6">

        {{-- Produit composé --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-night-100">Produit composé (résultat) *</label>
                <select wire:model.live="product_id"
                    class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                    <option value="">— Sélectionner un produit —</option>
                    @foreach ($compositeProducts as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} (vente: {{ number_format($p->selling_price, 0, ',', ' ') }} FCFA)</option>
                    @endforeach
                </select>
                @error('product_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-night-100">Description</label>
                <input wire:model="description" type="text"
                    class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
            </div>
        </div>

        {{-- Ingrédients --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-semibold text-night-100">Ingrédients *</h4>
                <button type="button" wire:click="addIngredient"
                    class="text-sm text-neon-400 hover:text-neon-200 font-medium">+ Ajouter ingrédient</button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-white/5 text-sm">
                    <thead class="bg-night-700">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-night-300 w-2/3">Ingrédient *</th>
                            <th class="px-3 py-2 text-right font-medium text-night-300 w-28">Quantité *</th>
                            <th class="px-3 py-2 text-right font-medium text-night-300 w-32">Coût ligne</th>
                            <th class="px-3 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($ingredients as $i => $ing)
                            <tr wire:key="ing-{{ $i }}">
                                <td class="px-3 py-2">
                                    <select wire:model.live="ingredients.{{ $i }}.product_id"
                                        class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                                        <option value="">— Sélectionner —</option>
                                        @foreach ($allProducts as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}
                                                (achat: {{ number_format($p->purchase_price, 0, ',', ' ') }}/{{ $p->unit->value }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("ingredients.{$i}.product_id") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </td>
                                <td class="px-3 py-2">
                                    <input wire:model.live="ingredients.{{ $i }}.quantity"
                                        type="number" step="0.0001" min="0.0001"
                                        class="block w-full border-white/10 rounded-md shadow-sm text-sm text-right focus:ring-neon-500/30 focus:border-neon-500">
                                    @error("ingredients.{$i}.quantity") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </td>
                                <td class="px-3 py-2 text-right font-medium text-night-100">
                                    {{ number_format($ing['cost'] ?? 0, 0, ',', ' ') }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @if (count($ingredients) > 1)
                                        <button type="button" wire:click="removeIngredient({{ $i }})"
                                            class="text-red-400 hover:text-red-400 text-lg leading-none">&times;</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Preview marge live --}}
        <div class="bg-neon-600/10 border border-neon-500/20 rounded-lg p-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center text-sm">
            <div>
                <div class="text-xs text-neon-400 mb-1 font-medium">Coût de revient</div>
                <div class="font-bold text-neon-400">{{ number_format($previewCost, 0, ',', ' ') }} FCFA</div>
            </div>
            <div>
                <div class="text-xs text-neon-400 mb-1 font-medium">Marge brute</div>
                <div class="font-bold {{ $previewMargin >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                    {{ number_format($previewMargin, 0, ',', ' ') }} FCFA
                </div>
            </div>
            <div>
                <div class="text-xs text-neon-400 mb-1 font-medium">Taux de marge</div>
                <div class="font-bold text-night-100">{{ number_format($previewMarginRate, 1) }}%</div>
            </div>
            <div>
                <div class="text-xs text-neon-400 mb-1 font-medium">Taux de marque</div>
                <div class="font-bold text-night-100">{{ number_format($previewMarkupRate, 1) }}%</div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input wire:model="is_active" type="checkbox" id="rec_active" class="rounded border-white/10 text-neon-400">
            <label for="rec_active" class="text-sm text-night-100">Recette active</label>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-white/8">
            <button type="button" wire:click="backToList"
                class="px-4 py-2 text-sm text-night-200 border border-white/10 rounded-md hover:bg-night-700">Annuler</button>
            <button type="submit" wire:loading.attr="disabled"
                class="px-4 py-2 text-sm text-white bg-neon-600 rounded-lg hover:bg-neon-500 disabled:opacity-50">
                <span wire:loading.remove>Enregistrer la recette</span>
                <span wire:loading>Enregistrement...</span>
            </button>
        </div>

    </form>

@endif
</div>
