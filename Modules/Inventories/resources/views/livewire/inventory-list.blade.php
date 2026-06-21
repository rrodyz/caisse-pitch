<div class="p-6">

    {{-- ── Nouveau inventaire ──────────────────────────────────────────────── --}}
    @can('manage-inventory')
        <div class="mb-6 p-4 bg-night-700 border border-white/8 rounded-lg flex items-end gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-night-200 mb-1">Notes (optionnel)</label>
                <input wire:model="notes" type="text" placeholder="Ex: Inventaire mensuel juin 2026"
                    class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
            </div>
            <button wire:click="createInventory" wire:loading.attr="disabled"
                class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 disabled:opacity-50 whitespace-nowrap">
                <span wire:loading.remove wire:target="createInventory">+ Démarrer un inventaire</span>
                <span wire:loading wire:target="createInventory">Initialisation...</span>
            </button>
        </div>
    @endcan

    {{-- ── Filtres ─────────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-end gap-3 mb-4">
        <input wire:model.live.debounce.300ms="searchList" type="text" placeholder="Référence..."
            class="border-white/10 rounded-md shadow-sm text-sm w-48 focus:ring-neon-500/30 focus:border-neon-500">

        <select wire:model.live="filterStatus"
            class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
            <option value="">Tous statuts</option>
            @foreach ($statuses as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
        </select>
    </div>

    {{-- ── Table ───────────────────────────────────────────────────────────── --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/5 text-sm">
            <thead class="bg-night-700">
                <tr>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Référence</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Statut</th>
                    <th class="px-3 py-3 text-center font-medium text-night-200">Produits</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Démarré par</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Date début</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Validé par</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Notes</th>
                    <th class="px-3 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse ($inventories as $inv)
                    <tr wire:key="inv-{{ $inv->id }}">
                        <td class="px-3 py-3 font-mono font-medium text-night-50">{{ $inv->reference }}</td>
                        <td class="px-3 py-3">
                            <span class="inline-flex px-2 py-0.5 text-xs rounded-full font-medium {{ $inv->status->badgeClass() }}">
                                {{ $inv->status->label() }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-center text-night-200">{{ $inv->items_count }}</td>
                        <td class="px-3 py-3 text-night-200 text-xs">{{ $inv->startedBy?->full_name ?? '—' }}</td>
                        <td class="px-3 py-3 text-night-300 text-xs">
                            {{ $inv->started_at?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td class="px-3 py-3 text-night-200 text-xs">{{ $inv->validatedBy?->full_name ?? '—' }}</td>
                        <td class="px-3 py-3 text-night-300 text-xs truncate max-w-xs">{{ $inv->notes ?? '—' }}</td>
                        <td class="px-3 py-3 text-right space-x-2 whitespace-nowrap">
                            @if ($inv->status->isEditable())
                                @can('manage-inventory')
                                    <button wire:click="openInventory({{ $inv->id }})"
                                        class="text-neon-400 hover:text-neon-200 text-xs font-medium">Saisir</button>
                                    <button wire:click="cancelInventory({{ $inv->id }})"
                                        wire:confirm="Annuler cet inventaire ?"
                                        class="text-red-500 hover:text-red-400 text-xs font-medium">Annuler</button>
                                @endcan
                            @else
                                <button wire:click="openInventory({{ $inv->id }})"
                                    class="text-night-300 hover:text-night-200 text-xs font-medium">Voir</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-night-300">Aucun inventaire.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $inventories->links() }}</div>

</div>
