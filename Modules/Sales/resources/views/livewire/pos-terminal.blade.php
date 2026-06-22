<div class="flex h-full bg-night-900 text-night-50">

    {{-- ═══════════════ COLONNE GAUCHE — Produits (62%) ═══════════════ --}}
    <div class="flex flex-col w-[62%] border-r border-white/5">

        {{-- Alerte session --}}
        @unless ($currentSession)
            <div class="mx-3 mt-3 p-3 bg-red-500/10 border border-red-500/30 rounded-xl text-red-300 text-sm text-center">
                Aucune session de caisse ouverte.
                <a href="{{ route('cash-sessions.index') }}" class="underline text-red-200 font-semibold ml-1">Ouvrir une session</a>
            </div>
        @endunless

        {{-- Succès --}}
        @if ($lastSaleId)
            <div class="mx-3 mt-3 p-2.5 bg-emerald-500/10 border border-emerald-500/25 rounded-xl text-emerald-300 text-sm flex items-center justify-between">
                <span>✓ Vente enregistrée</span>
                <a href="{{ route('tickets.show', $lastSaleId) }}" target="_blank"
                    class="text-emerald-200 underline text-xs font-medium">Imprimer ticket</a>
            </div>
        @endif

        {{-- Recherche + filtres catégories --}}
        <div class="px-3 pt-3 pb-2 space-y-2">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-night-300 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.200ms="search" type="text"
                    placeholder="Rechercher un produit..."
                    class="w-full bg-night-800 border border-white/8 rounded-xl pl-9 pr-4 py-2.5 text-sm text-night-50 placeholder-night-400
                           focus:outline-none focus:ring-2 focus:ring-neon-500/30 focus:border-neon-500 transition-colors">
            </div>

            <div class="flex flex-wrap gap-1.5">
                <button wire:click="$set('categoryId', null)"
                    class="px-3 py-1.5 text-xs rounded-lg font-semibold transition-all
                        {{ $categoryId === null
                            ? 'bg-gold-400 text-night-900 shadow-sm'
                            : 'bg-night-700 text-night-200 hover:bg-night-600 hover:text-night-100 border border-white/5' }}">
                    Tout
                </button>
                @foreach ($categories as $cat)
                    <button wire:click="$set('categoryId', {{ $cat->id }})"
                        class="px-3 py-1.5 text-xs rounded-lg font-semibold transition-all border"
                        style="{{ $categoryId === $cat->id
                            ? "background-color:{$cat->color};color:#08080f;border-color:{$cat->color}"
                            : "background-color:{$cat->color}18;color:{$cat->color};border-color:{$cat->color}40" }}">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Grille produits --}}
        <div class="flex-1 overflow-y-auto px-3 pb-3">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                @forelse ($products as $product)
                    @php $cardColor = $product->category?->color ?? '#8b5cf6'; @endphp
                    <button wire:click="addToCart({{ $product->id }})"
                        wire:key="prod-{{ $product->id }}"
                        class="product-card relative bg-night-800 border rounded-xl p-2.5 text-left group
                            {{ $product->isLowStock() ? 'border-amber-500/30' : 'border-white/6' }}"
                        style="--glow:{{ $cardColor }}40;border-top:2px solid {{ $cardColor }}88">
                        <div class="w-full aspect-square rounded-lg mb-2 flex items-center justify-center overflow-hidden"
                             style="background:{{ $cardColor }}12">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="" loading="lazy" class="w-full h-full object-cover">
                            @else
                                @php
                                    $ini = collect(explode(' ', $product->name))->filter()->take(2)->map(fn($w) => mb_substr($w, 0, 1))->join('');
                                @endphp
                                <div class="w-full h-full flex items-center justify-center font-black text-2xl tracking-tight"
                                     style="background:linear-gradient(145deg,{{ $cardColor }}60,{{ $cardColor }}20);color:{{ $cardColor }};text-shadow:0 2px 8px rgba(0,0,0,.6)">
                                    {{ mb_strtoupper($ini) }}
                                </div>
                            @endif
                        </div>
                        <div class="text-[13px] text-night-100 font-semibold leading-tight line-clamp-2 mb-1 group-hover:text-white transition-colors">
                            {{ $product->name }}
                        </div>
                        <div class="text-base font-bold" style="color:{{ $cardColor }}">
                            {{ number_format($product->selling_price, 0, ',', ' ') }}
                        </div>
                        @if ($product->isLowStock())
                            <div class="text-[10px] text-amber-400 mt-0.5 font-medium">⚠ {{ $product->stock_quantity }}</div>
                        @endif
                    </button>
                @empty
                    <div class="col-span-4 text-center text-night-300 py-16 text-sm">Aucun produit trouvé.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ═══════════════ COLONNE DROITE — Panier (38%) ══════════════════ --}}
    <div class="flex flex-col w-[38%] bg-night-950">

        {{-- Bandeau session caisse --}}
        @if ($currentSession)
            <div class="px-4 py-2 bg-emerald-500/8 border-b border-emerald-500/15 flex items-center gap-2 text-xs">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-sm shadow-emerald-400/60 shrink-0"></span>
                <span class="text-emerald-300 font-semibold truncate">{{ $currentSession->cashRegister?->name ?? 'Caisse' }}</span>
                <span class="text-night-300 truncate">· {{ $currentSession->openedBy?->full_name ?? $currentSession->openedBy?->first_name ?? '' }}</span>
                <span class="text-night-400 ml-auto shrink-0">depuis {{ \Carbon\Carbon::parse($currentSession->opened_at)->format('H:i') }}</span>
            </div>
        @else
            <div class="px-4 py-2 bg-red-500/8 border-b border-red-500/15 flex items-center gap-2 text-xs">
                <span class="w-1.5 h-1.5 rounded-full bg-red-400 shrink-0"></span>
                <span class="text-red-300 font-semibold">Aucune caisse ouverte</span>
            </div>
        @endif

        {{-- Header panier --}}
        <div class="px-4 py-3 border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h2 class="font-bold text-sm text-night-100">Panier</h2>
                @if (!empty($cart))
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-neon-600 text-white text-[10px] font-bold">
                        {{ count($cart) }}
                    </span>
                @endif
            </div>
            @if (!empty($cart))
                <button wire:click="clearCart" wire:confirm="Vider le panier ?"
                    class="text-xs text-night-300 hover:text-red-400 transition-colors">Vider</button>
            @endif
        </div>

        {{-- Sélection client --}}
        @php $selCustomer = $customerId ? $customers->firstWhere('id', $customerId) : null; @endphp
        <div class="px-3 py-2 border-b border-white/5">
            @if ($selCustomer)
                <div class="flex items-center gap-2 bg-neon-500/10 border border-neon-500/20 rounded-lg px-2.5 py-1.5">
                    <svg class="h-3.5 w-3.5 text-neon-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-xs font-semibold text-neon-300 truncate flex-1">{{ $selCustomer->name }}</span>
                    @if ($selCustomer->credit_limit > 0)
                        <span class="text-[10px] text-neon-400/70 shrink-0">
                            dispo {{ number_format($selCustomer->availableCredit(), 0, ',', ' ') }} FCFA
                        </span>
                    @endif
                    <button wire:click="$set('customerId', null)"
                        class="w-5 h-5 flex items-center justify-center text-night-400 hover:text-red-400 transition-colors text-base leading-none shrink-0">×</button>
                </div>
            @else
                <select wire:change="selectCustomer($event.target.value)"
                    class="w-full bg-night-800 border border-white/8 rounded-lg px-2.5 py-1.5 text-xs text-night-300 focus:outline-none focus:ring-1 focus:ring-neon-500 focus:border-neon-500 transition-colors">
                    <option value="">+ Client (optionnel)</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        {{-- Items panier --}}
        <div class="flex-1 overflow-y-auto divide-y divide-white/4">
            @forelse ($cart as $i => $item)
                <div wire:key="cart-{{ $i }}" class="px-3 py-2.5 flex items-center gap-2 hover:bg-white/[0.03] transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-night-50 truncate leading-tight">{{ $item['product_name'] }}</div>
                        <div class="text-xs text-night-400 mt-0.5">
                            {{ number_format($item['unit_price'], 0, ',', ' ') }} FCFA / u
                        </div>
                    </div>
                    <div class="flex items-center gap-0.5">
                        <button wire:click="updateQty({{ $i }}, {{ $item['quantity'] - 1 }})"
                            class="w-8 h-8 bg-night-700 hover:bg-night-600 active:bg-night-500 rounded-lg text-sm font-bold text-night-200 hover:text-white flex items-center justify-center transition-colors touch-manipulation">−</button>
                        <input wire:change="updateQty({{ $i }}, $event.target.value)"
                            type="number" value="{{ $item['quantity'] }}" min="0" step="1"
                            class="w-10 bg-transparent border-0 text-center text-sm font-bold text-white py-1 focus:outline-none">
                        <button wire:click="updateQty({{ $i }}, {{ $item['quantity'] + 1 }})"
                            class="w-8 h-8 bg-night-700 hover:bg-night-600 active:bg-night-500 rounded-lg text-sm font-bold text-night-200 hover:text-white flex items-center justify-center transition-colors touch-manipulation">+</button>
                    </div>
                    <div class="text-sm font-bold text-gold-400 w-16 text-right shrink-0 tabular-nums">
                        {{ number_format($item['total_price'], 0, ',', ' ') }}
                    </div>
                    <button wire:click="removeFromCart({{ $i }})"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-night-300 hover:text-white hover:bg-red-500/20 transition-colors touch-manipulation text-lg leading-none ml-0.5">×</button>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center flex-1 text-night-500 gap-3 py-10">
                    <div class="w-16 h-16 rounded-2xl bg-night-800 flex items-center justify-center">
                        <svg class="h-8 w-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span class="text-sm text-night-400">Panier vide</span>
                </div>
            @endforelse
        </div>

        {{-- Totaux + remise — Alpine gère l'affichage local, wire:model envoie au prochain action --}}
        <div x-data="{ discount: {{ (int) $discountAmount }}, subtotal: {{ (int) $subtotal }} }">
            <div class="border-t border-white/5 px-4 pt-3 pb-2 space-y-2" style="background:rgba(5,5,12,.7)">
                <div class="flex justify-between text-xs text-night-400">
                    <span>Sous-total</span>
                    <span class="text-night-300 tabular-nums">{{ number_format($subtotal, 0, ',', ' ') }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-night-400">Remise (FCFA)</span>
                    <input wire:model="discountAmount"
                        x-on:input="discount = parseFloat($event.target.value) || 0"
                        type="number" min="0" step="1"
                        class="w-24 bg-night-800 border border-white/8 rounded-lg px-2 py-1 text-xs text-right text-white focus:outline-none focus:ring-1 focus:ring-neon-500">
                </div>
                <div class="flex justify-between items-baseline border-t border-white/8 pt-2.5 mt-1">
                    <span class="text-sm font-semibold text-night-300 uppercase tracking-wider">Total</span>
                    <span class="text-2xl font-black tabular-nums text-gradient-gold"
                        x-text="new Intl.NumberFormat('fr-FR').format(Math.max(0, subtotal - discount))"></span>
                </div>
            </div>

            <div class="px-3 pb-3 pt-1.5">
                <button wire:click="openPayment"
                    @disabled(empty($cart) || !$currentSession)
                    class="w-full py-3.5 rounded-xl font-bold text-sm transition-all tracking-wide uppercase
                        {{ (empty($cart) || !$currentSession)
                            ? 'bg-night-700 text-night-400 cursor-not-allowed'
                            : 'text-night-900 active:scale-[0.98] glow-green' }}"
                    style="{{ (empty($cart) || !$currentSession) ? '' : 'background:linear-gradient(135deg,#10b981,#059669)' }}">
                    <span x-text="'Encaisser — ' + new Intl.NumberFormat('fr-FR').format(Math.max(0, subtotal - discount)) + ' FCFA'">
                        Encaisser — {{ number_format($total, 0, ',', ' ') }} FCFA
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════════ MODAL REÇU ═══════════════════════════════════════ --}}
    @if ($showReceipt && !empty($receiptData))
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm">
            <div class="bg-night-800 border border-white/10 rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden">

                {{-- Header succès --}}
                <div class="bg-emerald-600 px-6 py-4 text-center">
                    <div class="text-3xl mb-1">✓</div>
                    <div class="text-white font-bold text-lg">Vente enregistrée</div>
                    <div class="text-emerald-200 text-sm font-mono mt-0.5">{{ $receiptData['number'] }}</div>
                </div>

                {{-- Corps reçu --}}
                <div class="px-5 py-4 space-y-3 max-h-[55vh] overflow-y-auto">
                    <div class="text-xs text-night-300 text-center">
                        {{ $receiptData['created_at'] }}
                        @if($receiptData['served_by'])
                            · {{ $receiptData['served_by'] }}
                        @endif
                    </div>

                    {{-- Articles --}}
                    <div class="border-t border-b border-white/8 py-2.5 space-y-1.5">
                        @foreach ($receiptData['items'] as $item)
                            <div class="flex items-baseline gap-2 text-sm">
                                <span class="flex-1 text-night-200 truncate">{{ $item['product_name'] }}</span>
                                <span class="text-night-300 text-xs shrink-0">×{{ $item['quantity'] }}</span>
                                <span class="text-gold-400 font-semibold shrink-0 w-20 text-right">
                                    {{ number_format($item['total_price'], 0, ',', ' ') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Totaux --}}
                    <div class="space-y-1">
                        <div class="flex justify-between text-sm text-night-300">
                            <span>Sous-total</span>
                            <span>{{ number_format($receiptData['subtotal'], 0, ',', ' ') }} FCFA</span>
                        </div>
                        @if ($receiptData['discount'] > 0)
                            <div class="flex justify-between text-sm text-amber-400">
                                <span>Remise</span>
                                <span>−{{ number_format($receiptData['discount'], 0, ',', ' ') }} FCFA</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-base font-bold text-white border-t border-white/8 pt-2 mt-1">
                            <span>TOTAL</span>
                            <span class="text-gold-400">{{ number_format($receiptData['total'], 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>

                    {{-- Paiement --}}
                    <div class="bg-night-700/50 rounded-xl p-3 space-y-1.5 text-sm">
                        <div class="flex justify-between">
                            <span class="text-night-300">Mode</span>
                            <span class="font-semibold text-neon-300">{{ $receiptData['payment_label'] }}</span>
                        </div>
                        @if ($receiptData['payment_mode'] === 'cash')
                            <div class="flex justify-between text-night-200">
                                <span>Reçu</span>
                                <span>{{ number_format($receiptData['amount_given'], 0, ',', ' ') }} FCFA</span>
                            </div>
                            @if ($receiptData['change'] > 0)
                                <div class="flex justify-between text-emerald-400 font-semibold">
                                    <span>Monnaie rendue</span>
                                    <span>{{ number_format($receiptData['change'], 0, ',', ' ') }} FCFA</span>
                                </div>
                            @endif
                        @endif
                        @if ($receiptData['notes'])
                            <div class="text-night-300 text-xs pt-1.5 border-t border-white/8 italic">
                                {{ $receiptData['notes'] }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="px-5 pb-5 flex gap-3">
                    <button wire:click="closeReceipt"
                        class="flex-1 py-3 bg-night-700 hover:bg-night-600 text-night-200 rounded-xl text-sm font-semibold border border-white/8 transition-colors">
                        Nouveau
                    </button>
                    <a href="{{ route('tickets.show', $receiptData['id']) }}" target="_blank"
                        class="flex-1 py-3 bg-gold-400 hover:bg-gold-300 text-night-900 rounded-xl text-sm font-bold text-center transition-colors flex items-center justify-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Imprimer
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════ MODAL PAIEMENT ══════════════════════════════════ --}}
    @if ($showPayment)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm">
            <div class="bg-night-800 border border-white/10 rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">

                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-white">Encaissement</h3>
                    <button wire:click="$set('showPayment', false)" class="text-night-300 hover:text-night-200 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="bg-night-700/60 rounded-xl p-4 mb-5 border border-white/5">
                    <div class="flex justify-between text-sm text-night-200 mb-1">
                        <span>Sous-total</span>
                        <span>{{ number_format($subtotal, 0, ',', ' ') }} FCFA</span>
                    </div>
                    @if ($discountAmount > 0)
                        <div class="flex justify-between text-sm text-amber-400 mb-1">
                            <span>Remise</span>
                            <span>−{{ number_format($discountAmount, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-xl font-bold text-white border-t border-white/8 pt-2.5 mt-2">
                        <span>À payer</span>
                        <span class="text-gold-400">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-night-200 uppercase tracking-wider mb-2">Mode de paiement</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($paymentModes as $mode)
                            @php $active = $paymentMode === $mode['value']; @endphp
                            <button wire:click="$set('paymentMode', '{{ $mode['value'] }}')"
                                class="py-2.5 px-3 rounded-xl text-sm font-semibold border transition-all
                                    {{ $active ? 'text-white' : 'bg-night-700 border-white/8 text-night-200 hover:bg-night-600 hover:text-white' }}"
                                @if($active)
                                    style="background-color:{{ $mode['color'] }};border-color:{{ $mode['color'] }}"
                                @endif>
                                {{ $mode['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                @if (in_array($paymentMode, ['orange_money','moov_money','wave']))
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-night-200 uppercase tracking-wider mb-1.5">
                            N° téléphone client {{ $paymentMode === 'moov_money' ? '*' : '(optionnel)' }}
                        </label>
                        <input wire:model="paymentPhone" type="tel" inputmode="numeric"
                            class="w-full bg-night-700 border border-white/10 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-neon-500/30 focus:border-neon-500 placeholder-night-500"
                            placeholder="70 00 00 00">
                        <p class="text-xs text-night-300 mt-1.5">
                            @if ($paymentMode === 'moov_money')
                                Le client recevra une demande Moov Money sur son téléphone, à valider par code PIN.
                            @else
                                Le client scanne le QR / ouvre le lien pour payer sur son téléphone.
                            @endif
                        </p>
                    </div>
                @endif

                @if ($paymentMode === 'credit')
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-night-200 uppercase tracking-wider mb-1.5">Client *</label>
                        @if ($customerId)
                            @php $modalCustomer = $customers->firstWhere('id', $customerId); @endphp
                            <div class="flex items-center gap-2 bg-neon-500/10 border border-neon-500/20 rounded-xl px-3 py-2.5">
                                <svg class="h-4 w-4 text-neon-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold text-neon-300 truncate">{{ $modalCustomer?->name }}</div>
                                    @if ($modalCustomer && $modalCustomer->credit_limit > 0)
                                        <div class="text-xs text-neon-400/70">
                                            Dispo : {{ number_format($modalCustomer->availableCredit(), 0, ',', ' ') }} FCFA
                                            @if ($modalCustomer->current_credit > 0)
                                                · Doit : {{ number_format($modalCustomer->current_credit, 0, ',', ' ') }} FCFA
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <button wire:click="$set('customerId', null)" class="text-night-400 hover:text-red-400 transition-colors text-lg leading-none">×</button>
                            </div>
                        @else
                            <select wire:model.live="customerId"
                                class="w-full bg-night-700 border border-white/10 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-neon-500/30 focus:border-neon-500">
                                <option value="">— Sélectionner un client —</option>
                                @foreach ($customers as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->name }}
                                        @if($c->current_credit > 0)
                                            (doit: {{ number_format($c->current_credit, 0, ',', ' ') }} FCFA)
                                        @endif
                                        @if($c->credit_limit > 0)
                                            — dispo: {{ number_format($c->availableCredit(), 0, ',', ' ') }} FCFA
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        @error('customerId') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                @endif

                @if ($paymentMode === 'cash')
                    <div class="mb-4" x-data="{ amount: {{ (int) $amountGiven }}, total: {{ (int) $total }} }">
                        <label class="block text-xs font-semibold text-night-200 uppercase tracking-wider mb-1.5">Montant reçu (FCFA)</label>
                        <input wire:model="amountGiven"
                            x-on:input="amount = parseFloat($event.target.value) || 0"
                            type="number" step="1" min="0"
                            class="w-full bg-night-700 border border-white/10 rounded-xl px-4 py-3 text-xl text-white text-right font-bold focus:outline-none focus:ring-2 focus:ring-neon-500/30 focus:border-neon-500">
                        @error('amountGiven') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror

                        <div x-show="amount >= total" x-cloak
                            class="mt-2 p-3 bg-emerald-500/10 border border-emerald-500/25 rounded-xl text-center">
                            <span class="text-emerald-400 text-sm font-semibold">Monnaie à rendre :</span>
                            <span class="text-emerald-300 font-bold text-xl ml-2"
                                x-text="new Intl.NumberFormat('fr-FR').format(Math.max(0, amount - total)) + ' FCFA'"></span>
                        </div>
                    </div>
                @endif

                <div class="mb-5">
                    <label class="block text-xs font-semibold text-night-200 uppercase tracking-wider mb-1.5">Notes</label>
                    <input wire:model="saleNotes" type="text"
                        class="w-full bg-night-700 border border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-neon-500/30 focus:border-neon-500 placeholder-night-500"
                        placeholder="Table 5, client VIP...">
                </div>

                @error('cart') <div class="text-red-400 text-sm mb-3">{{ $message }}</div> @enderror

                <div class="flex gap-3">
                    <button wire:click="$set('showPayment', false)"
                        class="flex-1 py-3 bg-night-700 hover:bg-night-600 text-night-200 rounded-xl font-semibold text-sm border border-white/8 transition-colors">
                        Annuler
                    </button>
                    <button wire:click="confirmPayment" wire:loading.attr="disabled"
                        class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl disabled:opacity-50 transition-all text-sm active:scale-[0.98]">
                        @php $isGw = in_array($paymentMode, ['orange_money','moov_money','wave']); @endphp
                        <span wire:loading.remove wire:target="confirmPayment">{{ $isGw ? 'Lancer le paiement →' : '✓ Confirmer' }}</span>
                        <span wire:loading wire:target="confirmPayment">{{ $isGw ? 'Connexion…' : 'Enregistrement…' }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════ MODAL ATTENTE PAIEMENT MOBILE ════════════════════ --}}
    @if ($showGateway)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 backdrop-blur-sm"
             @if(!$gatewayError) wire:poll.3s="pollPayment" @endif>
            <div class="bg-night-800 border border-white/10 rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6 text-center">

                <div class="flex items-center justify-center gap-2 mb-1">
                    <h3 class="text-lg font-bold text-white">Paiement {{ ucfirst($gatewayProvider) }}</h3>
                </div>
                <div class="text-2xl font-bold text-gold-400 mb-4">{{ number_format($total, 0, ',', ' ') }} FCFA</div>

                @if ($gatewayError)
                    {{-- Échec --}}
                    <div class="w-16 h-16 mx-auto rounded-full bg-red-500/15 flex items-center justify-center text-3xl text-red-400 mb-3">✕</div>
                    <p class="text-red-300 text-sm mb-5">{{ $gatewayError }}</p>
                    <div class="flex gap-3">
                        <button wire:click="cancelGatewayPayment"
                            class="flex-1 py-3 bg-night-700 hover:bg-night-600 text-night-200 rounded-xl font-semibold text-sm border border-white/8 transition-colors">
                            Retour
                        </button>
                    </div>
                @else
                    {{-- En attente --}}
                    @if ($checkoutUrl)
                        <div class="bg-white rounded-xl p-3 inline-block mb-3 [&>svg]:w-44 [&>svg]:h-44 [&>svg]:block">
                            {!! $qrSvg !!}
                        </div>
                        <p class="text-night-200 text-sm mb-1">Le client scanne ce QR code</p>
                        <a href="{{ $checkoutUrl }}" target="_blank"
                           class="text-xs text-neon-300 hover:text-neon-200 underline break-all">ou ouvrir le lien de paiement</a>
                    @elseif ($gatewayProvider === 'moov')
                        <div class="w-16 h-16 mx-auto rounded-full bg-blue-500/15 flex items-center justify-center text-3xl mb-3">📲</div>
                        <p class="text-night-200 text-sm mb-1">Demande envoyée au {{ $paymentPhone ?: 'téléphone du client' }}</p>
                        <p class="text-night-300 text-xs">Le client valide avec son code PIN Moov Money.</p>
                    @else
                        <p class="text-night-300 text-sm mb-3">Initialisation du paiement…</p>
                    @endif

                    <div class="flex items-center justify-center gap-2 mt-4 mb-5 text-night-300 text-sm">
                        <svg class="animate-spin h-4 w-4 text-neon-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"/>
                        </svg>
                        En attente de confirmation du paiement…
                    </div>

                    <button wire:click="cancelGatewayPayment"
                        class="w-full py-3 bg-night-700 hover:bg-night-600 text-night-200 rounded-xl font-semibold text-sm border border-white/8 transition-colors">
                        Annuler
                    </button>
                @endif
            </div>
        </div>
    @endif

</div>
