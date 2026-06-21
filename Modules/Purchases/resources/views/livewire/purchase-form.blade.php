<div class="p-6 space-y-6">

    {{-- En-tête --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div>
            <label class="block text-sm font-medium text-night-100">N° achat</label>
            <input wire:model="number" type="text" readonly
                class="mt-1 block w-full border-white/10 bg-night-700 rounded-md shadow-sm text-sm text-night-200">
        </div>
        <div>
            <label class="block text-sm font-medium text-night-100">Date *</label>
            <input wire:model="date" type="date"
                class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
            @error('date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-night-100">Fournisseur</label>
            <select wire:model="supplier_id"
                class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                <option value="">— Sélectionner —</option>
                @foreach ($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-night-100">Mode de paiement</label>
            <select wire:model="payment_mode"
                class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                <option value="">— Sélectionner —</option>
                <option value="espèces">Espèces</option>
                <option value="virement">Virement</option>
                <option value="chèque">Chèque</option>
                <option value="mobile_money">Mobile Money</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-night-100">Statut paiement</label>
            <select wire:model="payment_status"
                class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                <option value="pending">En attente</option>
                <option value="partial">Partiel</option>
                <option value="paid">Payé</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-night-100">Frais annexes</label>
            <input wire:model.live="fees" type="number" step="1" min="0"
                class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
        </div>
        <div class="col-span-2">
            <label class="block text-sm font-medium text-night-100">Justificatif (PDF/image)</label>
            <input wire:model="receipt" type="file" accept=".pdf,.jpg,.jpeg,.png"
                class="mt-1 block w-full text-sm text-night-200">
            @error('receipt') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Lignes produits --}}
    <div>
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-base font-medium text-night-50">Lignes d'achat</h3>
            <button type="button" wire:click="addItem"
                class="text-sm text-neon-400 hover:text-neon-200 font-medium">+ Ajouter ligne</button>
        </div>

        @error('items') <div class="text-red-500 text-xs mb-2">{{ $message }}</div> @enderror

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/5 text-sm">
                <thead class="bg-night-700">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium text-night-300 w-2/5">Produit *</th>
                        <th class="px-3 py-2 text-right font-medium text-night-300 w-24">Quantité *</th>
                        <th class="px-3 py-2 text-right font-medium text-night-300 w-32">Prix unitaire *</th>
                        <th class="px-3 py-2 text-right font-medium text-night-300 w-32">Total</th>
                        <th class="px-3 py-2 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach ($items as $i => $item)
                        <tr wire:key="item-{{ $i }}">
                            <td class="px-3 py-2">
                                <select wire:model.live="items.{{ $i }}.product_id"
                                    class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                                    <option value="">— Produit —</option>
                                    @foreach ($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->code }})</option>
                                    @endforeach
                                </select>
                                @error("items.{$i}.product_id") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </td>
                            <td class="px-3 py-2">
                                <input wire:model.live="items.{{ $i }}.quantity" type="number" step="0.001" min="0.001"
                                    class="block w-full border-white/10 rounded-md shadow-sm text-sm text-right focus:ring-neon-500/30 focus:border-neon-500">
                                @error("items.{$i}.quantity") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </td>
                            <td class="px-3 py-2">
                                <input wire:model.live="items.{{ $i }}.unit_price" type="number" step="1" min="0"
                                    class="block w-full border-white/10 rounded-md shadow-sm text-sm text-right focus:ring-neon-500/30 focus:border-neon-500">
                                @error("items.{$i}.unit_price") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </td>
                            <td class="px-3 py-2 text-right font-medium text-night-50">
                                {{ number_format($item['total'] ?? 0, 0, ',', ' ') }}
                            </td>
                            <td class="px-3 py-2 text-center">
                                @if (count($items) > 1)
                                    <button type="button" wire:click="removeItem({{ $i }})"
                                        class="text-red-400 hover:text-red-400 text-lg leading-none">&times;</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Totaux --}}
    <div class="flex justify-end">
        <div class="w-64 space-y-2 text-sm">
            <div class="flex justify-between text-night-200">
                <span>Sous-total</span>
                <span class="font-medium">{{ number_format($subtotal, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="flex justify-between text-night-200">
                <span>Frais annexes</span>
                <span class="font-medium">{{ number_format($fees, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="flex justify-between text-base font-semibold text-night-50 pt-2 border-t border-white/8">
                <span>Total</span>
                <span>{{ number_format($total_amount, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    <div>
        <label class="block text-sm font-medium text-night-100">Notes internes</label>
        <textarea wire:model="notes" rows="2"
            class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500"></textarea>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-between pt-4 border-t border-white/8">
        <a href="{{ route('purchases.index') }}" class="text-sm text-night-300 hover:text-night-100">← Retour</a>
        <div class="flex gap-3">
            <button type="button" wire:click="save" wire:loading.attr="disabled"
                class="px-4 py-2 text-sm text-night-200 border border-white/10 rounded-md hover:bg-night-700 disabled:opacity-50">
                <span wire:loading wire:target="save">Enregistrement...</span>
                <span wire:loading.remove wire:target="save">Enregistrer brouillon</span>
            </button>
            @can('validate-purchases')
                <button type="button" wire:click="saveAndValidate" wire:loading.attr="disabled"
                    wire:confirm="Enregistrer et valider l'achat (le stock sera mis à jour) ?"
                    class="px-4 py-2 text-sm text-white bg-green-600 rounded-md hover:bg-green-700 disabled:opacity-50">
                    <span wire:loading wire:target="saveAndValidate">Validation...</span>
                    <span wire:loading.remove wire:target="saveAndValidate">Enregistrer & Valider</span>
                </button>
            @endcan
        </div>
    </div>

</div>
