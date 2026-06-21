<div class="p-6">

    {{-- ── Résumé période ───────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        @php $summary = $summary ?? []; @endphp
        <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-4 text-center">
            <div class="text-xs font-semibold text-red-400 mb-1">Pertes</div>
            <div class="text-2xl font-bold text-red-300">{{ isset($summary['loss']) ? number_format($summary['loss']->cnt) : 0 }}</div>
            <div class="text-xs text-red-400 mt-1">Coût : {{ isset($summary['loss']) ? number_format($summary['loss']->total, 0, ',', ' ') : 0 }} FCFA</div>
        </div>
        <div class="bg-amber-500/10 border border-amber-500/20 rounded-lg p-4 text-center">
            <div class="text-xs font-semibold text-amber-400 mb-1">Casses</div>
            <div class="text-2xl font-bold text-amber-300">{{ isset($summary['break']) ? number_format($summary['break']->cnt) : 0 }}</div>
            <div class="text-xs text-amber-400 mt-1">Coût : {{ isset($summary['break']) ? number_format($summary['break']->total, 0, ',', ' ') : 0 }} FCFA</div>
        </div>
        <div class="bg-purple-500/10 border border-purple-500/20 rounded-lg p-4 text-center">
            <div class="text-xs font-semibold text-purple-400 mb-1">Offerts</div>
            <div class="text-2xl font-bold text-purple-300">{{ isset($summary['gift']) ? number_format($summary['gift']->cnt) : 0 }}</div>
            <div class="text-xs text-purple-400 mt-1">Coût : {{ isset($summary['gift']) ? number_format($summary['gift']->total, 0, ',', ' ') : 0 }} FCFA</div>
        </div>
    </div>

    {{-- ── Filtres & bouton ─────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-end gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher produit..."
            class="border-white/10 rounded-md shadow-sm text-sm w-52 focus:ring-neon-500/30 focus:border-neon-500">

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

        @can('create-losses')
            <button wire:click="openCreate"
                class="ml-auto inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-md hover:bg-red-700">
                + Déclarer une perte
            </button>
        @endcan
    </div>

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/5 text-sm">
            <thead class="bg-night-700">
                <tr>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Date</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Type</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Produit</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">Qté</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">Coût unit.</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">Coût total</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Raison</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Déclaré par</th>
                    <th class="px-3 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse ($losses as $loss)
                    <tr wire:key="loss-{{ $loss->id }}">
                        <td class="px-3 py-3 text-night-300 text-xs whitespace-nowrap">
                            {{ $loss->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-3 py-3">
                            <span class="inline-flex px-2 py-0.5 text-xs rounded-full font-medium {{ $loss->type->badgeClass() }}">
                                {{ $loss->type->label() }}
                            </span>
                        </td>
                        <td class="px-3 py-3 font-medium text-night-50">{{ $loss->product?->name ?? '—' }}</td>
                        <td class="px-3 py-3 text-right text-night-100">{{ number_format($loss->quantity, 2) }}</td>
                        <td class="px-3 py-3 text-right text-night-200">
                            {{ $loss->unit_cost !== null ? number_format($loss->unit_cost, 0, ',', ' ') : '—' }}
                        </td>
                        <td class="px-3 py-3 text-right font-semibold text-red-400">
                            {{ number_format($loss->total_cost, 0, ',', ' ') }}
                        </td>
                        <td class="px-3 py-3 text-night-300 text-xs max-w-xs truncate">
                            {{ $loss->reason ?? '—' }}
                        </td>
                        <td class="px-3 py-3 text-night-300 text-xs">
                            {{ $loss->declaredBy?->full_name ?? '—' }}
                        </td>
                        <td class="px-3 py-3 text-right space-x-2 whitespace-nowrap">
                            @can('edit-losses')
                                <button wire:click="openEdit({{ $loss->id }})"
                                    class="text-neon-400 hover:text-neon-200 text-xs font-medium">Modifier</button>
                            @endcan
                            @can('delete-losses')
                                <button wire:click="delete({{ $loss->id }})"
                                    wire:confirm="Supprimer ? Le stock ne sera pas réajusté automatiquement."
                                    class="text-red-400 hover:text-red-400 text-xs font-medium">Suppr.</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-night-300">Aucune déclaration.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $losses->links() }}</div>

    {{-- ── Modal ────────────────────────────────────────────────────────────── --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-night-800 rounded-xl shadow-2xl w-full max-w-lg mx-4 p-6">

                <h3 class="text-lg font-semibold text-night-50 mb-4">
                    {{ $editingId ? 'Modifier la déclaration' : 'Nouvelle déclaration' }}
                </h3>

                @if (!$editingId)
                    <p class="text-xs text-amber-300 bg-amber-500/10 border border-amber-500/20 rounded p-2 mb-4">
                        La quantité sera immédiatement déduite du stock.
                    </p>
                @endif

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-night-200 mb-1">Type *</label>
                            <select wire:model="formType"
                                class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                                @foreach ($types as $t)
                                    <option value="{{ $t['value'] }}">{{ $t['label'] }}</option>
                                @endforeach
                            </select>
                            @error('formType') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-night-200 mb-1">Produit *</label>
                            <select wire:model.live="formProduct"
                                class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                                <option value="">— Sélectionner —</option>
                                @foreach ($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} (stock: {{ $p->stock_quantity }})</option>
                                @endforeach
                            </select>
                            @error('formProduct') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-night-200 mb-1">Quantité *</label>
                            <input wire:model="formQty" type="number" step="0.0001" min="0.0001"
                                class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                            @error('formQty') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-night-200 mb-1">
                                Coût unitaire
                                <span class="text-night-300 font-normal">(FCFA)</span>
                            </label>
                            <input wire:model="formUnitCost" type="number" step="1" min="0"
                                class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-night-200 mb-1">Raison</label>
                        <input wire:model="formReason" type="text" placeholder="Périmé, renversé, brisé..."
                            class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-night-200 mb-1">Notes internes</label>
                        <textarea wire:model="formNotes" rows="2"
                            class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-white/8">
                    <button wire:click="$set('showModal', false)"
                        class="px-4 py-2 text-sm text-night-200 border border-white/10 rounded-md hover:bg-night-700">
                        Annuler
                    </button>
                    <button wire:click="save" wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm text-white bg-red-600 rounded-md hover:bg-red-700 disabled:opacity-50">
                        <span wire:loading.remove>{{ $editingId ? 'Mettre à jour' : 'Déclarer & déduire stock' }}</span>
                        <span wire:loading>Enregistrement...</span>
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
