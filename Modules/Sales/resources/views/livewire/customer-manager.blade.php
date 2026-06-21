<div class="p-6">

    {{-- En-tête --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Nom, téléphone, email..."
                class="border-white/10 rounded-md shadow-sm text-sm w-56 focus:ring-neon-500/30 focus:border-neon-500">
            <label class="inline-flex items-center gap-1.5 text-sm text-night-200">
                <input wire:model.live="showInactive" type="checkbox" class="rounded border-white/10">
                Inactifs
            </label>
        </div>
        @can('create-customers')
            <button wire:click="openCreateForm"
                class="px-4 py-2 bg-neon-600 text-white text-sm font-semibold rounded-lg hover:bg-neon-500">
                + Nouveau client
            </button>
        @endcan
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/5 text-sm">
            <thead class="bg-night-700">
                <tr>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Nom</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Téléphone</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Email</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">Limite crédit</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">Encours</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">Disponible</th>
                    <th class="px-3 py-3 text-center font-medium text-night-200">Statut</th>
                    <th class="px-3 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($customers as $customer)
                    <tr wire:key="customer-{{ $customer->id }}">
                        <td class="px-3 py-3 font-medium text-night-50">
                            <button wire:click="viewCustomer({{ $customer->id }})"
                                class="hover:text-neon-400 underline text-left">{{ $customer->name }}</button>
                        </td>
                        <td class="px-3 py-3 text-night-200">{{ $customer->phone ?? '—' }}</td>
                        <td class="px-3 py-3 text-night-200">{{ $customer->email ?? '—' }}</td>
                        <td class="px-3 py-3 text-right text-night-100">{{ number_format($customer->credit_limit, 0, ',', ' ') }}</td>
                        <td class="px-3 py-3 text-right {{ $customer->current_credit > 0 ? 'text-red-400 font-semibold' : 'text-night-200' }}">
                            {{ number_format($customer->current_credit, 0, ',', ' ') }}
                        </td>
                        <td class="px-3 py-3 text-right {{ $customer->availableCredit() > 0 ? 'text-emerald-400' : 'text-night-300' }}">
                            {{ number_format($customer->availableCredit(), 0, ',', ' ') }}
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex px-2 py-0.5 text-xs rounded-full font-medium
                                {{ $customer->is_active ? 'bg-emerald-500/15 text-emerald-300' : 'bg-night-700 text-night-300' }}">
                                {{ $customer->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-right space-x-2 whitespace-nowrap">
                            @if($customer->current_credit > 0)
                                @can('create-customers')
                                    <button wire:click="openPayment({{ $customer->id }})"
                                        class="text-emerald-400 hover:text-emerald-400 text-xs font-medium">Encaisser</button>
                                @endcan
                            @endif
                            @can('edit-customers')
                                <button wire:click="openEditForm({{ $customer->id }})"
                                    class="text-indigo-500 hover:text-neon-400 text-xs font-medium">Modifier</button>
                            @endcan
                            @can('delete-customers')
                                <button wire:click="deleteCustomer({{ $customer->id }})"
                                    wire:confirm="Supprimer ce client ?"
                                    class="text-red-500 hover:text-red-400 text-xs font-medium">Supprimer</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-night-300">Aucun client.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $customers->links() }}</div>

    {{-- Modal créer/modifier --}}
    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-night-800 rounded-xl shadow-2xl w-full max-w-lg mx-4 p-6">
                <h3 class="text-lg font-semibold text-night-50 mb-4">
                    {{ $editingId ? 'Modifier le client' : 'Nouveau client' }}
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-night-200 mb-1">Nom *</label>
                        <input wire:model="form.name" type="text"
                            class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                        @error('form.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-night-200 mb-1">Téléphone</label>
                        <input wire:model="form.phone" type="text"
                            class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-night-200 mb-1">Email</label>
                        <input wire:model="form.email" type="email"
                            class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-night-200 mb-1">Limite crédit (FCFA)</label>
                        <input wire:model="form.credit_limit" type="number" min="0" step="100"
                            class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                        @error('form.credit_limit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex items-end">
                        <label class="inline-flex items-center gap-2 text-sm text-night-100">
                            <input wire:model="form.is_active" type="checkbox" class="rounded border-white/10">
                            Client actif
                        </label>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-night-200 mb-1">Notes</label>
                        <textarea wire:model="form.notes" rows="2"
                            class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-5">
                    <button wire:click="$set('showForm', false)"
                        class="px-4 py-2 text-sm text-night-200 border border-white/10 rounded-md hover:bg-night-700">Annuler</button>
                    <button wire:click="save" wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm text-white bg-neon-600 rounded-lg hover:bg-neon-500 disabled:opacity-50">
                        <span wire:loading.remove wire:target="save">Enregistrer</span>
                        <span wire:loading wire:target="save">Enregistrement...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal encaissement crédit --}}
    @if($showPaymentModal && $payingCustomer)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-night-800 rounded-xl shadow-2xl w-full max-w-md mx-4 p-6">
                <h3 class="text-lg font-semibold text-night-50 mb-2">Encaissement crédit</h3>
                <div class="bg-night-700 border border-white/10 rounded p-3 mb-4 text-sm">
                    <div class="font-medium text-night-50">{{ $payingCustomer->name }}</div>
                    <div class="text-night-200 mt-1">
                        Encours : <strong>{{ number_format($payingCustomer->current_credit, 0, ',', ' ') }} FCFA</strong>
                    </div>
                </div>
                <div class="space-y-3 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-night-200 mb-1">Montant à encaisser *</label>
                        <input wire:model="paymentForm.amount" type="number" min="0" step="100"
                            class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                        @error('paymentForm.amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-night-200 mb-1">Mode de paiement</label>
                        <select wire:model="paymentForm.payment_mode"
                            class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                            <option value="cash">Espèces</option>
                            <option value="card">Carte</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-night-200 mb-1">Notes</label>
                        <input wire:model="paymentForm.notes" type="text"
                            class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500"
                            placeholder="Référence reçu, commentaire...">
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showPaymentModal', false)"
                        class="px-4 py-2 text-sm text-night-200 border border-white/10 rounded-md hover:bg-night-700">Annuler</button>
                    <button wire:click="confirmPayment" wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm text-white bg-green-600 rounded-md hover:bg-green-700 disabled:opacity-50">
                        <span wire:loading.remove wire:target="confirmPayment">Valider l'encaissement</span>
                        <span wire:loading wire:target="confirmPayment">Validation...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
