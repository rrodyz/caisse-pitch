<div class="p-6">

    {{-- ── Alertes stock faible ──────────────────────────────────────────────── --}}
    @if ($lowStock->isNotEmpty())
        <div class="mb-4 p-3 bg-amber-500/10 border border-amber-500/30 rounded-lg">
            <p class="text-sm font-semibold text-amber-300 mb-1">Produits sous seuil minimum</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($lowStock as $p)
                    <span class="text-xs px-2 py-1 bg-amber-500/15 text-amber-300 rounded-full">
                        {{ $p->name }} — {{ $p->stock_quantity }} {{ $p->unit->value }}
                        (min: {{ $p->min_stock }})
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Onglets ───────────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-1 mb-5 border-b border-white/8">
        <button wire:click="$set('tab','stock')"
            class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors -mb-px
                   {{ $tab === 'stock' ? 'border-neon-500 text-neon-300' : 'border-transparent text-night-300 hover:text-night-200' }}">
            État du stock
        </button>
        <button wire:click="$set('tab','journal')"
            class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors -mb-px
                   {{ $tab === 'journal' ? 'border-neon-500 text-neon-300' : 'border-transparent text-night-300 hover:text-night-200' }}">
            Journal des mouvements
        </button>
    </div>

    {{-- ── Formulaire mouvement manuel (visible quelle que soit l'onglet) ─────── --}}
    @if ($showForm)
        <div class="mb-6 p-4 bg-night-700 border border-white/8 rounded-lg">
            <h4 class="text-sm font-semibold text-night-200 mb-3">Mouvement manuel</h4>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-night-200 mb-1">Produit *</label>
                    <select wire:model="formProduct"
                        class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                        <option value="">— Sélectionner —</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (stock: {{ $p->stock_quantity }})</option>
                        @endforeach
                    </select>
                    @error('formProduct') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-night-200 mb-1">Type *</label>
                    <select wire:model="formType"
                        class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                        <option value="manual_in">Entrée manuelle</option>
                        <option value="manual_out">Sortie manuelle</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-night-200 mb-1">Quantité *</label>
                    <input wire:model="formQty" type="number" step="0.0001" min="0.0001"
                        class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                    @error('formQty') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-night-200 mb-1">Notes</label>
                    <input wire:model="formNotes" type="text"
                        class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                </div>
            </div>
            <div class="flex gap-2 mt-3">
                <button wire:click="saveMovement"
                    class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Enregistrer</button>
                <button wire:click="$set('showForm', false)"
                    class="px-3 py-1.5 text-sm border border-white/10 text-night-200 rounded-md hover:bg-night-700">Annuler</button>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════════
         ONGLET : ÉTAT DU STOCK
    ══════════════════════════════════════════════════════════════════════════ --}}
    @if ($tab === 'stock')
        {{-- Barre de filtres --}}
        <div class="flex flex-wrap items-end gap-3 mb-4">
            <input wire:model.live.debounce.300ms="stockSearch" type="text"
                placeholder="Rechercher un produit..."
                class="border-white/10 rounded-md shadow-sm text-sm w-56 focus:ring-neon-500/30 focus:border-neon-500">

            <div class="flex gap-1">
                @foreach (['' => 'Tous', 'ok' => 'OK', 'low' => 'Sous seuil', 'empty' => 'Épuisés'] as $val => $lbl)
                    @php
                        $active = $stockFilter === $val;
                        $cls = $active
                            ? 'bg-neon-600 text-white border-neon-600'
                            : 'bg-night-700 text-night-300 border-white/10 hover:border-white/20 hover:text-night-200';
                    @endphp
                    <button wire:click="$set('stockFilter','{{ $val }}')"
                        class="px-3 py-1.5 text-xs font-medium border rounded-lg transition-colors {{ $cls }}">
                        {{ $lbl }}
                    </button>
                @endforeach
            </div>

            @can('adjust-stock')
                <button wire:click="openForm"
                    class="ml-auto inline-flex items-center px-4 py-2 bg-neon-600 text-white text-sm font-semibold rounded-lg hover:bg-neon-500">
                    + Mouvement manuel
                </button>
            @endcan
        </div>

        {{-- Tableau état du stock --}}
        @php
            $totalValue = $stockProducts->sum(fn($p) => $p->stock_quantity * ($p->purchase_price ?? 0));
        @endphp

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/5 text-sm">
                <thead class="bg-night-700">
                    <tr>
                        <th class="px-3 py-3 text-left font-medium text-night-200">Produit</th>
                        <th class="px-3 py-3 text-left font-medium text-night-200">Catégorie</th>
                        <th class="px-3 py-3 text-right font-medium text-night-200">Stock actuel</th>
                        <th class="px-3 py-3 text-right font-medium text-night-200">Seuil min</th>
                        <th class="px-3 py-3 text-center font-medium text-night-200">Statut</th>
                        <th class="px-3 py-3 text-right font-medium text-night-200">Valeur stock</th>
                        @can('adjust-stock')
                            <th class="px-3 py-3 text-center font-medium text-night-200">Actions</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($stockProducts as $p)
                        @php
                            if ($p->stock_quantity <= 0) {
                                $statusBadge = 'bg-red-500/15 text-red-300';
                                $statusLabel = 'Épuisé';
                            } elseif ($p->min_stock > 0 && $p->stock_quantity <= $p->min_stock) {
                                $statusBadge = 'bg-amber-500/15 text-amber-300';
                                $statusLabel = 'Sous seuil';
                            } else {
                                $statusBadge = 'bg-emerald-500/15 text-emerald-300';
                                $statusLabel = 'OK';
                            }
                            $catHex    = $p->category?->color ?? '#6366f1';
                            $stockValue = $p->stock_quantity * ($p->purchase_price ?? 0);
                        @endphp
                        <tr wire:key="sp-{{ $p->id }}" class="hover:bg-night-700/40 transition-colors">
                            <td class="px-3 py-3 font-medium text-night-50">{{ $p->name }}</td>
                            <td class="px-3 py-3">
                                @if ($p->category)
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs rounded-full font-medium"
                                          style="background-color:{{ $catHex }}26;color:{{ $catHex }}">
                                        {{ $p->category->name }}
                                    </span>
                                @else
                                    <span class="text-night-300">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-right font-semibold
                                {{ $p->stock_quantity <= 0 ? 'text-red-400' : ($p->min_stock > 0 && $p->stock_quantity <= $p->min_stock ? 'text-amber-400' : 'text-emerald-400') }}">
                                {{ number_format($p->stock_quantity, 2) }}
                                <span class="text-xs text-night-300 font-normal ml-0.5">{{ $p->unit->value }}</span>
                            </td>
                            <td class="px-3 py-3 text-right text-night-300">
                                {{ $p->min_stock > 0 ? number_format($p->min_stock, 2) : '—' }}
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 text-xs rounded-full font-medium {{ $statusBadge }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-right text-night-200">
                                @if ($p->purchase_price)
                                    {{ number_format($stockValue, 0, ',', ' ') }} FCFA
                                @else
                                    <span class="text-night-300">—</span>
                                @endif
                            </td>
                            @can('adjust-stock')
                                <td class="px-3 py-3 text-center">
                                    <div class="inline-flex gap-1">
                                        <button wire:click="quickAdjust({{ $p->id }}, 'manual_in')"
                                            class="w-7 h-7 flex items-center justify-center rounded-md bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 transition-colors font-bold text-sm"
                                            title="Entrée stock">+</button>
                                        <button wire:click="quickAdjust({{ $p->id }}, 'manual_out')"
                                            class="w-7 h-7 flex items-center justify-center rounded-md bg-red-500/10 text-red-400 hover:bg-red-500/20 transition-colors font-bold text-sm"
                                            title="Sortie stock">−</button>
                                    </div>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-night-300">
                                Aucun produit trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($stockProducts->isNotEmpty())
                    <tfoot class="bg-night-700/50">
                        <tr>
                            <td colspan="5" class="px-3 py-2.5 text-xs font-medium text-night-300">
                                {{ $stockProducts->count() }} produit(s)
                            </td>
                            <td class="px-3 py-2.5 text-right text-sm font-semibold text-night-200">
                                {{ number_format($totalValue, 0, ',', ' ') }} FCFA
                            </td>
                            @can('adjust-stock')
                                <td></td>
                            @endcan
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

    {{-- ══════════════════════════════════════════════════════════════════════════
         ONGLET : JOURNAL DES MOUVEMENTS
    ══════════════════════════════════════════════════════════════════════════ --}}
    @else
        {{-- Filtres journal --}}
        <div class="flex flex-wrap items-end gap-3 mb-4">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher un produit..."
                class="border-white/10 rounded-md shadow-sm text-sm w-56 focus:ring-neon-500/30 focus:border-neon-500">

            <select wire:model.live="filterType"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                <option value="">Tous types</option>
                @foreach ($types as $t)
                    <option value="{{ $t['value'] }}">{{ $t['label'] }}</option>
                @endforeach
            </select>

            <input wire:model.live="dateFrom" type="date"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
            <span class="text-night-300 text-sm">→</span>
            <input wire:model.live="dateTo" type="date"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">

            @can('adjust-stock')
                <button wire:click="openForm"
                    class="ml-auto inline-flex items-center px-4 py-2 bg-neon-600 text-white text-sm font-semibold rounded-lg hover:bg-neon-500">
                    + Mouvement manuel
                </button>
            @endcan
        </div>

        {{-- Table mouvements --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/5 text-sm">
                <thead class="bg-night-700">
                    <tr>
                        <th class="px-3 py-3 text-left font-medium text-night-200">Date</th>
                        <th class="px-3 py-3 text-left font-medium text-night-200">Produit</th>
                        <th class="px-3 py-3 text-left font-medium text-night-200">Type</th>
                        <th class="px-3 py-3 text-right font-medium text-night-200">Avant</th>
                        <th class="px-3 py-3 text-right font-medium text-night-200">Δ</th>
                        <th class="px-3 py-3 text-right font-medium text-night-200">Après</th>
                        <th class="px-3 py-3 text-left font-medium text-night-200">Notes</th>
                        <th class="px-3 py-3 text-left font-medium text-night-200">Opérateur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($movements as $m)
                        @php $delta = $m->quantity_after - $m->quantity_before; @endphp
                        <tr wire:key="mv-{{ $m->id }}">
                            <td class="px-3 py-3 text-night-300 whitespace-nowrap text-xs">
                                {{ $m->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-3 py-3 font-medium text-night-50">
                                {{ $m->product?->name ?? '—' }}
                            </td>
                            <td class="px-3 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 text-xs rounded-full font-medium {{ $m->type->badgeClass() }}">
                                    {{ $m->type->label() }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-right text-night-200">
                                {{ number_format($m->quantity_before, 2) }}
                            </td>
                            <td class="px-3 py-3 text-right font-semibold {{ $delta >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $delta >= 0 ? '+' : '' }}{{ number_format($delta, 2) }}
                            </td>
                            <td class="px-3 py-3 text-right font-medium text-night-50">
                                {{ number_format($m->quantity_after, 2) }}
                            </td>
                            <td class="px-3 py-3 text-night-300 text-xs max-w-xs truncate">
                                {{ $m->notes ?? '—' }}
                            </td>
                            <td class="px-3 py-3 text-night-300 text-xs">
                                {{ $m->user?->full_name ?? 'Système' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-night-300">Aucun mouvement.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $movements->links() }}</div>
    @endif

</div>
