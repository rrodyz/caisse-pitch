<div class="p-6">
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="N° achat..."
            class="border-white/10 rounded-md shadow-sm text-sm w-36 focus:ring-neon-500/30 focus:border-neon-500">
        <select wire:model.live="filterStatus"
            class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
            <option value="">Tous statuts</option>
            <option value="draft">Brouillon</option>
            <option value="validated">Validé</option>
            <option value="cancelled">Annulé</option>
        </select>
        <select wire:model.live="filterSupplier"
            class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
            <option value="">Tous fournisseurs</option>
            @foreach ($suppliers as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
            @endforeach
        </select>
        <input wire:model.live="filterFrom" type="date"
            class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
        <input wire:model.live="filterTo" type="date"
            class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/5 text-sm">
            <thead class="bg-night-700">
                <tr>
                    <th class="px-3 py-3 text-left font-medium text-night-200 uppercase tracking-wider">N°</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Date</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Fournisseur</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200 uppercase tracking-wider">Total</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Statut</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Paiement</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Créé par</th>
                    <th class="px-3 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse ($purchases as $purchase)
                    <tr wire:key="purch-{{ $purchase->id }}">
                        <td class="px-3 py-3 font-mono text-xs">
                            <a href="{{ route('purchases.show', $purchase) }}" class="text-neon-400 hover:underline">{{ $purchase->number }}</a>
                        </td>
                        <td class="px-3 py-3 text-night-200">{{ $purchase->date->format('d/m/Y') }}</td>
                        <td class="px-3 py-3 text-night-50">{{ $purchase->supplier?->name ?? '—' }}</td>
                        <td class="px-3 py-3 text-right font-medium text-night-50">
                            {{ number_format($purchase->total_amount, 0, ',', ' ') }}
                        </td>
                        <td class="px-3 py-3">
                            @php $colors = ['draft'=>'bg-amber-500/15 text-amber-300','validated'=>'bg-emerald-500/15 text-emerald-300','cancelled'=>'bg-red-500/15 text-red-300']; @endphp
                            <span class="inline-flex items-center px-2 py-1 text-xs rounded-full font-medium {{ $colors[$purchase->status] ?? '' }}">
                                {{ ['draft'=>'Brouillon','validated'=>'Validé','cancelled'=>'Annulé'][$purchase->status] }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-xs text-night-200">
                            {{ ['pending'=>'En attente','partial'=>'Partiel','paid'=>'Payé'][$purchase->payment_status] ?? '' }}
                            @if($purchase->payment_mode)
                                <div class="text-night-300">{{ $purchase->payment_mode }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-xs text-night-200">{{ $purchase->creator?->full_name ?? '—' }}</td>
                        <td class="px-3 py-3 text-right space-x-1 whitespace-nowrap">
                            <a href="{{ route('purchases.show', $purchase) }}" class="text-night-300 hover:text-night-200 text-xs">Voir</a>
                            @if($purchase->isDraft())
                                @can('edit-purchases')
                                    <a href="{{ route('purchases.edit', $purchase) }}" class="text-neon-400 hover:text-neon-200 text-xs">Modifier</a>
                                @endcan
                                @can('validate-purchases')
                                    <button wire:click="validatePurchase({{ $purchase->id }})"
                                        wire:confirm="Valider et mettre à jour le stock ?"
                                        class="text-emerald-400 hover:text-emerald-400 text-xs font-medium">Valider</button>
                                    <button wire:click="cancelPurchase({{ $purchase->id }})"
                                        wire:confirm="Annuler cet achat ?"
                                        class="text-red-400 hover:text-red-400 text-xs">Annuler</button>
                                @endcan
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-night-300">Aucun achat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $purchases->links() }}</div>
</div>
