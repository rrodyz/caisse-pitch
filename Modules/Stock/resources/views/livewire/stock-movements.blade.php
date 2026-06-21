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

    {{-- ── Filtres ───────────────────────────────────────────────────────────── --}}
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

    {{-- ── Formulaire mouvement manuel ─────────────────────────────────────── --}}
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

    {{-- ── Table mouvements ─────────────────────────────────────────────────── --}}
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

</div>
