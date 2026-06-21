<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('purchases.index') }}" class="text-night-300 hover:text-night-100">← Achats</a>
                <h2 class="font-semibold text-xl text-night-50">Achat {{ $purchase->number }}</h2>
                @php $colors = ['draft'=>'bg-amber-500/15 text-amber-300','validated'=>'bg-emerald-500/15 text-emerald-300','cancelled'=>'bg-red-500/15 text-red-300']; @endphp
                <span class="inline-flex items-center px-3 py-1 text-sm rounded-full font-medium {{ $colors[$purchase->status] ?? '' }}">
                    {{ ['draft'=>'Brouillon','validated'=>'Validé','cancelled'=>'Annulé'][$purchase->status] }}
                </span>
            </div>
            @if($purchase->isDraft())
                @can('edit-purchases')
                    <a href="{{ route('purchases.edit', $purchase) }}"
                        class="inline-flex items-center px-4 py-2 bg-neon-600 text-white text-sm font-semibold rounded-md hover:bg-neon-500">
                        Modifier
                    </a>
                @endcan
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-night-800 border border-white/5 rounded-xl p-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div><div class="text-night-300">Date</div><div class="font-medium">{{ $purchase->date->format('d/m/Y') }}</div></div>
                    <div><div class="text-night-300">Fournisseur</div><div class="font-medium">{{ $purchase->supplier?->name ?? '—' }}</div></div>
                    <div><div class="text-night-300">Mode paiement</div><div class="font-medium">{{ $purchase->payment_mode ?? '—' }}</div></div>
                    <div><div class="text-night-300">Statut paiement</div>
                        <div class="font-medium">{{ ['pending'=>'En attente','partial'=>'Partiel','paid'=>'Payé'][$purchase->payment_status] }}</div>
                    </div>
                    @if($purchase->isValidated())
                        <div><div class="text-night-300">Validé par</div><div class="font-medium">{{ $purchase->validator?->full_name ?? '—' }}</div></div>
                        <div><div class="text-night-300">Validé le</div><div class="font-medium">{{ $purchase->validated_at?->format('d/m/Y H:i') }}</div></div>
                    @endif
                    <div><div class="text-night-300">Créé par</div><div class="font-medium">{{ $purchase->creator?->full_name ?? '—' }}</div></div>
                </div>
                @if($purchase->receipt_path)
                    <div class="mt-4 pt-3 border-t border-white/8">
                        <a href="{{ route('purchases.receipt', $purchase) }}"
                            class="inline-flex items-center gap-1.5 text-xs text-neon-400 hover:text-neon-200">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                            </svg>
                            Télécharger le justificatif
                        </a>
                    </div>
                @endif
                @if($purchase->notes)
                    <div class="mt-4 text-sm text-night-200 border-t border-white/8 pt-3">{{ $purchase->notes }}</div>
                @endif
            </div>

            <div class="bg-night-800 border border-white/5 rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-white/5 text-sm">
                    <thead class="bg-night-700">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-night-300">Produit</th>
                            <th class="px-4 py-3 text-right font-medium text-night-300">Quantité</th>
                            <th class="px-4 py-3 text-right font-medium text-night-300">Prix unitaire</th>
                            <th class="px-4 py-3 text-right font-medium text-night-300">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($purchase->items as $item)
                            <tr>
                                <td class="px-4 py-3">{{ $item->product_name }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($item->quantity, 3) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right font-medium">{{ number_format($item->total_price, 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-night-700 border-t border-white/8">
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-right text-night-200 text-xs">Sous-total</td>
                            <td class="px-4 py-2 text-right font-medium">{{ number_format($purchase->subtotal, 0, ',', ' ') }}</td>
                        </tr>
                        @if($purchase->fees > 0)
                            <tr>
                                <td colspan="3" class="px-4 py-2 text-right text-night-200 text-xs">Frais annexes</td>
                                <td class="px-4 py-2 text-right font-medium">{{ number_format($purchase->fees, 0, ',', ' ') }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right font-semibold text-night-50">Total</td>
                            <td class="px-4 py-3 text-right font-bold text-night-50 text-base">
                                {{ number_format($purchase->total_amount, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
