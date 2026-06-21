<div class="p-6">

    {{-- Navigation --}}
    <div class="mb-5">
        <button wire:click="backToList" class="text-sm text-neon-400 hover:text-neon-200 font-medium">
            ← Retour aux clients
        </button>
    </div>

    @if($customer)
        {{-- En-tête client --}}
        <div class="bg-night-800 border border-white/8 rounded-xl p-5 mb-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-xl font-bold text-night-50">{{ $customer->name }}</h2>
                    <div class="flex items-center gap-4 mt-1 text-sm text-night-200">
                        @if($customer->phone)
                            <span>📞 {{ $customer->phone }}</span>
                        @endif
                        @if($customer->email)
                            <span>✉ {{ $customer->email }}</span>
                        @endif
                    </div>
                    @if($customer->notes)
                        <p class="mt-2 text-xs text-night-300 italic">{{ $customer->notes }}</p>
                    @endif
                </div>
                <span class="inline-flex px-2 py-1 text-xs rounded-full font-medium
                    {{ $customer->is_active ? 'bg-emerald-500/15 text-emerald-300' : 'bg-night-700 text-night-300' }}">
                    {{ $customer->is_active ? 'Actif' : 'Inactif' }}
                </span>
            </div>

            {{-- Indicateurs crédit --}}
            <div class="grid grid-cols-3 gap-4 mt-5">
                <div class="bg-night-700 rounded-lg p-4 text-center">
                    <div class="text-xs text-night-300 mb-1">Limite crédit</div>
                    <div class="text-lg font-bold text-night-100">{{ number_format($customer->credit_limit, 0, ',', ' ') }}</div>
                    <div class="text-xs text-night-300">FCFA</div>
                </div>
                <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-4 text-center">
                    <div class="text-xs text-red-400 mb-1">Encours</div>
                    <div class="text-lg font-bold text-red-300">{{ number_format($customer->current_credit, 0, ',', ' ') }}</div>
                    <div class="text-xs text-red-400">FCFA</div>
                </div>
                <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-lg p-4 text-center">
                    <div class="text-xs text-emerald-400 mb-1">Disponible</div>
                    <div class="text-lg font-bold text-emerald-400">{{ number_format($customer->availableCredit(), 0, ',', ' ') }}</div>
                    <div class="text-xs text-green-400">FCFA</div>
                </div>
            </div>

            @if($customer->current_credit > 0)
                @can('create-customers')
                    <div class="mt-4">
                        <button wire:click="openPayment({{ $customer->id }})"
                            class="px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-md hover:bg-green-700">
                            Encaisser le crédit
                        </button>
                    </div>
                @endcan
            @endif
        </div>

        {{-- Onglets --}}
        <div class="border-b border-white/8 mb-4">
            <nav class="-mb-px flex space-x-6">
                <button wire:click="$set('activeTab', 'sales')"
                    class="pb-3 text-sm font-medium border-b-2 {{ $activeTab === 'sales' ? 'border-indigo-500 text-neon-400' : 'border-transparent text-night-300 hover:text-night-200 hover:border-white/10' }}">
                    Historique ventes ({{ $salesTotal }})
                </button>
                <button wire:click="$set('activeTab', 'payments')"
                    class="pb-3 text-sm font-medium border-b-2 {{ $activeTab === 'payments' ? 'border-indigo-500 text-neon-400' : 'border-transparent text-night-300 hover:text-night-200 hover:border-white/10' }}">
                    Encaissements crédit ({{ $paymentsTotal }})
                </button>
            </nav>
        </div>

        {{-- Historique ventes --}}
        @if($activeTab === 'sales')
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-white/5 text-sm">
                    <thead class="bg-night-700">
                        <tr>
                            <th class="px-3 py-3 text-left font-medium text-night-200">N°</th>
                            <th class="px-3 py-3 text-left font-medium text-night-200">Date</th>
                            <th class="px-3 py-3 text-center font-medium text-night-200">Art.</th>
                            <th class="px-3 py-3 text-right font-medium text-night-200">Remise</th>
                            <th class="px-3 py-3 text-right font-medium text-night-200">Total</th>
                            <th class="px-3 py-3 text-left font-medium text-night-200">Mode</th>
                            <th class="px-3 py-3 text-left font-medium text-night-200">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($sales as $sale)
                            <tr wire:key="s-{{ $sale->id }}" class="{{ $sale->status->value !== 'completed' ? 'opacity-60' : '' }}">
                                <td class="px-3 py-3 font-mono text-xs font-medium">
                                    <a href="{{ route('tickets.show', $sale->id) }}" target="_blank"
                                        class="text-neon-400 hover:underline">{{ $sale->number }}</a>
                                </td>
                                <td class="px-3 py-3 text-xs text-night-200">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-3 py-3 text-center text-night-200">{{ $sale->items_count }}</td>
                                <td class="px-3 py-3 text-right text-yellow-600 text-xs">
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
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-night-300">Aucune vente.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $sales->links() }}</div>
        @endif

        {{-- Encaissements crédit --}}
        @if($activeTab === 'payments')
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-white/5 text-sm">
                    <thead class="bg-night-700">
                        <tr>
                            <th class="px-3 py-3 text-left font-medium text-night-200">Date</th>
                            <th class="px-3 py-3 text-right font-medium text-night-200">Montant</th>
                            <th class="px-3 py-3 text-left font-medium text-night-200">Mode</th>
                            <th class="px-3 py-3 text-left font-medium text-night-200">Vente liée</th>
                            <th class="px-3 py-3 text-left font-medium text-night-200">Reçu par</th>
                            <th class="px-3 py-3 text-left font-medium text-night-200">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($payments as $payment)
                            <tr wire:key="p-{{ $payment->id }}">
                                <td class="px-3 py-3 text-xs text-night-200">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-3 py-3 text-right font-semibold text-emerald-400">
                                    {{ number_format($payment->amount, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-3 py-3 text-xs text-night-200">{{ ucfirst(str_replace('_', ' ', $payment->payment_mode)) }}</td>
                                <td class="px-3 py-3 text-xs text-night-200">
                                    @if($payment->sale)
                                        <a href="{{ route('tickets.show', $payment->sale_id) }}" target="_blank"
                                            class="text-neon-400 hover:underline font-mono">{{ $payment->sale->number }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-xs text-night-200">{{ $payment->receivedBy?->full_name ?? '—' }}</td>
                                <td class="px-3 py-3 text-xs text-night-200">{{ $payment->notes ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-night-300">Aucun encaissement.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $payments->links() }}</div>
        @endif

        {{-- Modal encaissement crédit (réutilisé depuis CustomerManager) --}}
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
                            <label class="block text-sm font-medium text-night-200 mb-1">Montant *</label>
                            <input wire:model="paymentForm.amount" type="number" min="0" step="100"
                                class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                            @error('paymentForm.amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-night-200 mb-1">Mode</label>
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
                                class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button wire:click="$set('showPaymentModal', false)"
                            class="px-4 py-2 text-sm text-night-200 border border-white/10 rounded-md hover:bg-night-700">Annuler</button>
                        <button wire:click="confirmPayment" wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm text-white bg-green-600 rounded-md hover:bg-green-700 disabled:opacity-50">
                            <span wire:loading.remove wire:target="confirmPayment">Valider</span>
                            <span wire:loading wire:target="confirmPayment">Validation...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif

</div>
