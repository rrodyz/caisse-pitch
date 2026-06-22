<div class="p-6">

    {{-- Barre d'outils --}}
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Nom ou code..."
            class="border-white/10 rounded-md shadow-sm text-sm w-52 focus:ring-neon-500/30 focus:border-neon-500">

        <select wire:model.live="filterCategory"
            class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
            <option value="">Toutes catégories</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterStatus"
            class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
            <option value="">Tous statuts</option>
            <option value="active">Actifs</option>
            <option value="inactive">Inactifs</option>
            <option value="low">Stock bas</option>
        </select>

        @can('create-products')
            <button wire:click="openCreate"
                class="ml-auto inline-flex items-center px-4 py-2 bg-neon-600 text-white text-sm font-semibold rounded-lg hover:bg-neon-500">
                + Nouveau produit
            </button>
        @endcan
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/5 text-sm">
            <thead class="bg-night-700">
                <tr>
                    <th class="px-3 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Code</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Produit</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Catégorie</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200 uppercase tracking-wider">P. Achat</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200 uppercase tracking-wider">P. Vente</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200 uppercase tracking-wider">Marge</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200 uppercase tracking-wider">Tx marge</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200 uppercase tracking-wider">Stock</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Unité</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Statut</th>
                    <th class="px-3 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse ($products as $product)
                    <tr wire:key="prod-{{ $product->id }}" class="hover:bg-white/[0.04] transition-colors {{ $loop->even ? 'bg-white/[0.02]' : '' }} {{ $product->is_active ? '' : 'opacity-50' }}">
                        <td class="px-3 py-3 font-mono text-xs text-night-200">{{ $product->code }}</td>
                        <td class="px-3 py-3">
                            <div class="flex items-center gap-2">
                                @if ($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" loading="lazy" class="w-8 h-8 rounded object-cover">
                                @else
                                    @php
                                        $c = $product->category?->color ?? '#8b5cf6';
                                        $ini = collect(explode(' ', $product->name))->filter()->take(2)->map(fn($w) => mb_substr($w, 0, 1))->join('');
                                    @endphp
                                    <div class="w-8 h-8 rounded flex items-center justify-center text-[10px] font-bold"
                                         style="background:{{ $c }}44;color:{{ $c }}">{{ mb_strtoupper($ini) }}</div>
                                @endif
                                <div>
                                    <div class="font-medium text-night-50">{{ $product->name }}</div>
                                    @if ($product->isLowStock())
                                        <span class="text-xs text-red-400 font-medium">⚠ Stock bas</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            @if ($product->category)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium text-white"
                                    style="background-color: {{ $product->category->color }}">
                                    {{ $product->category->name }}
                                </span>
                            @else
                                <span class="text-night-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-right text-night-200">{{ number_format($product->purchase_price, 0, ',', ' ') }}</td>
                        <td class="px-3 py-3 text-right font-medium text-night-50">{{ number_format($product->selling_price, 0, ',', ' ') }}</td>
                        <td class="px-3 py-3 text-right {{ $product->margin >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ number_format($product->margin, 0, ',', ' ') }}
                        </td>
                        <td class="px-3 py-3 text-right text-night-300 text-xs">
                            {{ number_format($product->margin_rate, 1) }}%
                            <div class="text-night-300">{{ number_format($product->markup_rate, 1) }}%*</div>
                        </td>
                        <td class="px-3 py-3 text-right {{ $product->isLowStock() ? 'text-red-400 font-semibold' : 'text-night-50' }}">
                            {{ $product->stock_quantity }}
                            <div class="text-xs text-night-300">min: {{ $product->min_stock }}</div>
                        </td>
                        <td class="px-3 py-3 text-night-300 capitalize">{{ $product->unit->value }}</td>
                        <td class="px-3 py-3">
                            <button wire:click="toggleActive({{ $product->id }})"
                                class="inline-flex items-center px-2 py-1 text-xs rounded-full font-medium
                                    {{ $product->is_active ? 'bg-emerald-500/15 text-emerald-300' : 'bg-night-700 text-night-200' }}">
                                {{ $product->is_active ? 'Actif' : 'Inactif' }}
                            </button>
                        </td>
                        <td class="px-3 py-3 text-right space-x-2 whitespace-nowrap">
                            @can('edit-products')
                                <button wire:click="openEdit({{ $product->id }})" class="text-neon-400 hover:text-neon-200 text-xs font-medium">Modifier</button>
                            @endcan
                            @can('delete-products')
                                <button wire:click="confirmDelete({{ $product->id }})" class="text-red-400/60 hover:text-red-400 text-xs font-medium transition-colors">Supprimer</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="px-4 py-8 text-center text-night-300">Aucun produit trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 text-xs text-night-300">* Tx marge = (marge / achat)×100 &nbsp;|&nbsp; *Tx marque = (marge / vente)×100</div>
    <div class="mt-2">{{ $products->links() }}</div>

    {{-- Modal produit --}}
    @if ($showModal)
    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
        <div class="bg-night-800 rounded-xl border border-white/8 shadow-2xl w-full max-w-2xl flex flex-col max-h-[92vh]">

            {{-- Header fixe --}}
            <div class="px-6 py-4 border-b border-white/8 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-semibold text-night-50">{{ $editingId ? 'Modifier' : 'Nouveau' }} produit</h3>
                <button type="button" wire:click="closeModal" class="text-night-300 hover:text-night-200 transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Corps scrollable --}}
            <form wire:submit="save" class="flex flex-col flex-1 min-h-0">
                <div class="overflow-y-auto flex-1 p-6 space-y-4">

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-night-200 mb-1">Code *</label>
                            <input wire:model="code" type="text" class="block w-full border-white/10 rounded-lg shadow-sm text-sm uppercase focus:ring-neon-500/30 focus:border-neon-500">
                            @error('code') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-night-200 mb-1">Unité *</label>
                            <select wire:model="unit" class="block w-full border-white/10 rounded-lg shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                                @foreach ($units as $u)
                                    <option value="{{ $u['value'] }}">{{ $u['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-night-200 mb-1">Désignation *</label>
                        <input wire:model="name" type="text" class="block w-full border-white/10 rounded-lg shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                        @error('name') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-night-200 mb-1">Catégorie</label>
                        <select wire:model="category_id" class="block w-full border-white/10 rounded-lg shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                            <option value="">— Sans catégorie —</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-night-200 mb-1">Prix d'achat *</label>
                            <input wire:model.live="purchase_price" type="number" step="1" min="0"
                                class="block w-full border-white/10 rounded-lg shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                            @error('purchase_price') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-night-200 mb-1">Prix de vente *</label>
                            <input wire:model.live="selling_price" type="number" step="1" min="0"
                                class="block w-full border-white/10 rounded-lg shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                            @error('selling_price') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="bg-night-700 rounded-lg p-3 grid grid-cols-3 gap-3 text-center text-sm">
                        <div>
                            <div class="text-xs text-night-300 mb-1">Marge</div>
                            <div class="font-semibold {{ $previewMargin >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ number_format($previewMargin, 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-night-300 mb-1">Taux de marge</div>
                            <div class="font-semibold text-night-100">{{ number_format($previewMarginRate, 1) }}%</div>
                        </div>
                        <div>
                            <div class="text-xs text-night-300 mb-1">Taux de marque</div>
                            <div class="font-semibold text-night-100">{{ number_format($previewMarkupRate, 1) }}%</div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-night-200 mb-1">Stock minimum</label>
                        <input wire:model="min_stock" type="number" min="0"
                            class="block w-full border-white/10 rounded-lg shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-night-200 mb-1">Image</label>
                        <input wire:model="image" type="file" accept="image/*"
                            class="block w-full text-sm text-night-200">
                        @error('image') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-night-200 mb-1">Notes</label>
                        <textarea wire:model="notes" rows="2"
                            class="block w-full border-white/10 rounded-lg shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500"></textarea>
                    </div>

                    <div class="flex items-center gap-2">
                        <input wire:model="is_active" type="checkbox" id="prod_active" class="rounded border-white/10 text-neon-400 bg-night-700">
                        <label for="prod_active" class="text-sm text-night-200">Produit actif</label>
                    </div>
                </div>

                {{-- Footer fixe --}}
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-white/8 flex-shrink-0">
                    <button type="button" wire:click="closeModal"
                        class="px-4 py-2 text-sm text-night-200 border border-white/10 rounded-lg hover:bg-night-700 transition-colors">
                        Annuler
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm text-white bg-neon-600 rounded-lg hover:bg-neon-500 disabled:opacity-50 transition-colors">
                        <span wire:loading.remove>Enregistrer</span>
                        <span wire:loading>Enregistrement...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Modal confirmation suppression --}}
    @if ($showDelete)
    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
        <div class="bg-night-800 rounded-xl border border-white/8 shadow-2xl w-full max-w-sm p-6">
            <h3 class="text-lg font-semibold text-night-50 mb-2">Supprimer ce produit ?</h3>
            <p class="text-sm text-night-200 mb-4">Cette action est irréversible.</p>
            <div class="flex justify-end gap-3">
                <button wire:click="$set('showDelete', false)"
                    class="px-4 py-2 text-sm text-night-200 border border-white/10 rounded-lg hover:bg-night-700 transition-colors">Annuler</button>
                <button wire:click="delete"
                    class="px-4 py-2 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">Supprimer</button>
            </div>
        </div>
    </div>
    @endif

</div>
