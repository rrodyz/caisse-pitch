<div class="p-6">

    {{-- Filtres --}}
    <div class="flex flex-wrap items-end gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="N° vente ou client..."
            class="border-white/10 rounded-md shadow-sm text-sm w-48 focus:ring-neon-500/30 focus:border-neon-500">
        <select wire:model.live="filterStatus"
            class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
            <option value="">Tous statuts</option>
            @foreach($statuses as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterMode"
            class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
            <option value="">Tous modes</option>
            <option value="cash">Espèces</option>
            <option value="card">Carte</option>
            <option value="mobile_money">Mobile Money</option>
            <option value="orange_money">Orange Money</option>
            <option value="moov_money">Moov Money</option>
            <option value="wave">Wave</option>
            <option value="credit">Crédit</option>
        </select>
        <input wire:model.live="dateFrom" type="date"
            class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
        <span class="text-night-300 text-sm">→</span>
        <input wire:model.live="dateTo" type="date"
            class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/5 text-sm">
            <thead class="bg-night-700">
                <tr>
                    <th class="px-3 py-3 text-left font-medium text-night-200">N°</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Date</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Caissier</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Client</th>
                    <th class="px-3 py-3 text-center font-medium text-night-200">Art.</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">Remise</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">Total</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Mode</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Statut</th>
                    <th class="px-3 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($sales as $sale)
                    <tr wire:key="sale-{{ $sale->id }}"
                        class="{{ $sale->status->value !== 'completed' ? 'opacity-60' : '' }}">
                        <td class="px-3 py-3 font-mono text-xs font-medium text-night-50">
                            <a href="{{ route('tickets.show', $sale->id) }}" target="_blank"
                                class="hover:text-neon-400 underline">{{ $sale->number }}</a>
                        </td>
                        <td class="px-3 py-3 text-xs text-night-200">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-3 text-xs text-night-200">{{ $sale->servedBy?->full_name }}</td>
                        <td class="px-3 py-3 text-xs text-night-200">{{ $sale->customer?->name ?? '—' }}</td>
                        <td class="px-3 py-3 text-center text-night-200">{{ $sale->items_count }}</td>
                        <td class="px-3 py-3 text-right text-amber-400 text-xs">
                            {{ $sale->discount_amount > 0 ? number_format($sale->discount_amount, 0, ',', ' ') : '—' }}
                        </td>
                        <td class="px-3 py-3 text-right font-semibold text-night-50">
                            {{ number_format($sale->total_amount, 0, ',', ' ') }}
                        </td>
                        <td class="px-3 py-3 text-xs text-night-200">{{ $sale->payment_mode->label() }}</td>
                        <td class="px-3 py-3">
                            <span class="inline-flex px-2 py-0.5 text-xs rounded-full font-medium {{ $sale->status->badgeClass() }}">
                                {{ $sale->status->label() }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-right space-x-2 whitespace-nowrap">
                            @can('reprint-tickets')
                                <a href="{{ route('tickets.show', $sale->id) }}" target="_blank"
                                    class="text-indigo-500 hover:text-neon-400 text-xs font-medium">Ticket</a>
                            @endcan
                            @if($sale->status->value === 'completed')
                                @can('cancel-sales')
                                    <button wire:click="openCancelModal({{ $sale->id }})"
                                        class="text-red-400 hover:text-red-400 text-xs font-medium">
                                        Annuler
                                        @if($settings->supervisor_approval_threshold && $sale->total_amount > $settings->supervisor_approval_threshold)
                                            ⚠
                                        @endif
                                    </button>
                                @endcan
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-4 py-8 text-center text-night-300">Aucune vente.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $sales->links() }}</div>

    {{-- Modal annulation --}}
    @if($showCancelModal && $cancelSale)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-night-800 rounded-xl shadow-2xl w-full max-w-md mx-4 p-6">
                <h3 class="text-lg font-semibold text-night-50 mb-2">Annuler la vente</h3>
                <div class="bg-red-500/10 border border-red-500/25 rounded p-3 mb-4 text-sm text-red-300">
                    <strong>{{ $cancelSale->number }}</strong> —
                    {{ number_format($cancelSale->total_amount, 0, ',', ' ') }} FCFA
                    @if($settings->supervisor_approval_threshold && $cancelSale->total_amount > $settings->supervisor_approval_threshold)
                        <div class="mt-1 text-xs text-red-500">⚠ Montant supérieur au seuil de supervision ({{ number_format($settings->supervisor_approval_threshold, 0, ',', ' ') }} FCFA)</div>
                    @endif
                </div>
                <p class="text-xs text-amber-300 bg-amber-500/10 border border-amber-500/20 rounded p-2 mb-4">
                    Le stock sera automatiquement restitué.
                    @if($cancelSale->payment_mode->value === 'credit' && $cancelSale->customer)
                        Le crédit de {{ $cancelSale->customer->name }} sera réduit.
                    @endif
                </p>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-night-200 mb-1">Motif d'annulation *</label>
                    <textarea wire:model="cancelReason" rows="2"
                        class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-red-500 focus:border-red-500"
                        placeholder="Erreur de saisie, remboursement client..."></textarea>
                    @error('cancelReason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showCancelModal', false)"
                        class="px-4 py-2 text-sm text-night-200 border border-white/10 rounded-md hover:bg-night-700">Retour</button>
                    <button wire:click="confirmCancel" wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm text-white bg-red-600 rounded-md hover:bg-red-700 disabled:opacity-50">
                        <span wire:loading.remove>Confirmer l'annulation</span>
                        <span wire:loading>Annulation...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
