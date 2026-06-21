<div class="p-6">

    {{-- ── En-tête inventaire ──────────────────────────────────────────────── --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <button wire:click="backToList" class="text-night-300 hover:text-night-200 text-sm mb-2 block">← Inventaires</button>
            <h3 class="text-xl font-bold text-night-50 font-mono">{{ $inventory->reference }}</h3>
            <div class="flex items-center gap-3 mt-1">
                <span class="inline-flex px-2 py-0.5 text-xs rounded-full font-medium {{ $inventory->status->badgeClass() }}">
                    {{ $inventory->status->label() }}
                </span>
                <span class="text-xs text-night-200">
                    Démarré par {{ $inventory->startedBy?->full_name ?? '—' }}
                    le {{ $inventory->started_at?->format('d/m/Y H:i') }}
                </span>
                @if ($inventory->validated_at)
                    <span class="text-xs text-emerald-400">
                        | Validé par {{ $inventory->validatedBy?->full_name ?? '—' }}
                        le {{ $inventory->validated_at->format('d/m/Y H:i') }}
                    </span>
                @endif
            </div>
        </div>

        @if ($inventory->status->isEditable())
            @can('manage-inventory')
                <div class="flex gap-2">
                    <button wire:click="saveCounts" wire:loading.attr="disabled"
                        class="px-3 py-2 text-sm border border-neon-500/30 text-neon-400 rounded-md hover:bg-neon-600/10 disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveCounts">Sauvegarder</span>
                        <span wire:loading wire:target="saveCounts">Sauvegarde...</span>
                    </button>
                    <button wire:click="validateInventory" wire:loading.attr="disabled"
                        wire:confirm="Valider l'inventaire ? Le stock sera immédiatement ajusté pour tous les produits comptés."
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50">
                        <span wire:loading.remove wire:target="validateInventory">✓ Valider & Réconcilier</span>
                        <span wire:loading wire:target="validateInventory">Validation en cours...</span>
                    </button>
                </div>
            @endcan
        @endif
    </div>

    {{-- ── Barre de progression ─────────────────────────────────────────────── --}}
    <div class="mb-6 flex items-center gap-6">
        <div class="flex-1 bg-night-700 rounded-full h-2">
            @php $pct = $totalCount > 0 ? round(($countedCount / $totalCount) * 100) : 0; @endphp
            <div class="bg-neon-500 h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
        </div>
        <span class="text-sm text-night-200 whitespace-nowrap">
            {{ $countedCount }} / {{ $totalCount }} produits comptés ({{ $pct }}%)
        </span>
        <span class="text-sm font-semibold {{ $totalGapCost < 0 ? 'text-red-400' : 'text-emerald-400' }}">
            Écart valorisé : {{ number_format($totalGapCost, 0, ',', ' ') }} FCFA
        </span>
    </div>

    {{-- ── Recherche ────────────────────────────────────────────────────────── --}}
    <div class="mb-3">
        <input wire:model.live.debounce.300ms="searchItems" type="text" placeholder="Filtrer les produits..."
            class="border-white/10 rounded-md shadow-sm text-sm w-64 focus:ring-neon-500/30 focus:border-neon-500">
    </div>

    {{-- ── Table de comptage ───────────────────────────────────────────────── --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/5 text-sm">
            <thead class="bg-night-700 sticky top-0">
                <tr>
                    <th class="px-3 py-3 text-left font-medium text-night-300 w-2/5">Produit</th>
                    <th class="px-3 py-3 text-right font-medium text-night-300 w-28">Théorique</th>
                    <th class="px-3 py-3 text-right font-medium text-night-300 w-36">Compté *</th>
                    <th class="px-3 py-3 text-right font-medium text-night-300 w-24">Écart</th>
                    <th class="px-3 py-3 text-right font-medium text-night-300 w-32">Écart (FCFA)</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse ($items as $item)
                    @php
                        $inCounts  = isset($counts[$item->product_id]);
                        $counted   = $inCounts ? (float) $counts[$item->product_id] : $item->counted_quantity;
                        $gap       = $counted !== null ? $counted - $item->theoretical_quantity : $item->gap;
                        $gapCost   = $counted !== null ? $gap * ($item->unit_cost ?? 0) : $item->gap_cost;
                    @endphp
                    <tr wire:key="ii-{{ $item->id }}"
                        class="{{ $item->counted_quantity !== null ? 'bg-emerald-500/5' : '' }}">
                        <td class="px-3 py-2">
                            <div class="font-medium text-night-50">{{ $item->product?->name ?? '—' }}</div>
                            <div class="text-xs text-night-300">
                                {{ $item->product?->unit?->value }}
                                @if($item->unit_cost)
                                    · Achat: {{ number_format($item->unit_cost, 0, ',', ' ') }} FCFA
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-2 text-right text-night-200">
                            {{ number_format($item->theoretical_quantity, 2) }}
                        </td>
                        <td class="px-3 py-2 text-right">
                            @if ($inventory->status->isEditable())
                                <input
                                    wire:model.live="counts.{{ $item->product_id }}"
                                    type="number" step="0.01" min="0"
                                    placeholder="{{ $item->counted_quantity !== null ? number_format($item->counted_quantity, 2) : '—' }}"
                                    class="w-24 border-white/10 rounded-md shadow-sm text-sm text-right focus:ring-neon-500/30 focus:border-neon-500">
                            @else
                                <span class="{{ $item->counted_quantity !== null ? 'text-night-50 font-medium' : 'text-night-300' }}">
                                    {{ $item->counted_quantity !== null ? number_format($item->counted_quantity, 2) : '—' }}
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right font-semibold
                            {{ $gap < 0 ? 'text-red-400' : ($gap > 0 ? 'text-emerald-400' : 'text-night-300') }}">
                            @if ($counted !== null)
                                {{ $gap >= 0 ? '+' : '' }}{{ number_format($gap, 2) }}
                            @else
                                <span class="text-night-300">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right text-xs
                            {{ $gapCost < 0 ? 'text-red-500' : ($gapCost > 0 ? 'text-emerald-400' : 'text-night-300') }}">
                            @if ($counted !== null)
                                {{ number_format($gapCost, 0, ',', ' ') }}
                            @else
                                <span class="text-night-300">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-xs text-night-300">{{ $item->notes ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-night-300">Aucun produit.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $items->links() }}</div>

    @if ($inventory->status->isEditable())
        <p class="mt-2 text-xs text-night-300">
            * Laisser vide = non compté (ignoré lors de la validation). Cliquez Sauvegarder pour conserver les saisies en cours.
        </p>
    @endif

</div>
