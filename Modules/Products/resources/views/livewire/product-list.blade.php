<div>

    {{-- ── Barre de stats ───────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-3 px-5 pt-4 pb-3 border-b border-white/5">

        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold"
             style="background:rgba(139,92,246,.12);color:#a78bfa">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
            </svg>
            {{ $totalActive }} produit{{ $totalActive !== 1 ? 's' : '' }} actif{{ $totalActive !== 1 ? 's' : '' }}
        </div>

        @if ($lowStockCount > 0)
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold"
             style="background:rgba(239,68,68,.12);color:#f87171">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            {{ $lowStockCount }} en stock bas
        </div>
        @endif

        <div class="ml-auto flex items-center gap-2">
            {{-- Résultats filtrés --}}
            @if ($search || $filterCategory || $filterStatus)
            <span class="text-xs text-night-300">{{ $products->total() }} résultat{{ $products->total() !== 1 ? 's' : '' }}</span>
            @endif
        </div>
    </div>

    {{-- ── Barre de recherche / filtres ─────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-2 px-5 py-3 border-b border-white/5" style="background:rgba(5,5,12,.4)">

        {{-- Recherche --}}
        <div class="relative flex-1 min-w-[180px] max-w-xs">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 pointer-events-none" style="color:#545470"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Nom ou code…"
                   class="w-full pl-9 pr-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-neon-500/30 focus:border-neon-500 transition-colors"
                   style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#e0e0ee">
        </div>

        {{-- Catégorie --}}
        <select wire:model.live="filterCategory"
                class="py-2 pl-3 pr-8 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-neon-500/30 focus:border-neon-500"
                style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#88889a">
            <option value="">Toutes catégories</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>

        {{-- Statut --}}
        <select wire:model.live="filterStatus"
                class="py-2 pl-3 pr-8 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-neon-500/30 focus:border-neon-500"
                style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#88889a">
            <option value="">Tous statuts</option>
            <option value="active">Actifs</option>
            <option value="inactive">Inactifs</option>
            <option value="low">Stock bas</option>
        </select>

        @can('create-products')
        <button wire:click="openCreate"
                class="ml-auto flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold text-white transition-all active:scale-95"
                style="background:linear-gradient(135deg,#5b21b6,#7c3aed);box-shadow:0 4px 16px rgba(109,40,217,.3)">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Nouveau produit
        </button>
        @endcan
    </div>

    {{-- ── Table ─────────────────────────────────────────────────────────────── --}}
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr style="background:rgba(22,22,37,.9);border-bottom:1px solid rgba(255,255,255,.06)">
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color:#545470">Code</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color:#545470">Produit</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color:#545470">Catégorie</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">P. Achat</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">P. Vente</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">Marge</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">Stock</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color:#545470">Unité</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider" style="color:#545470">Statut</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                <tr wire:key="prod-{{ $product->id }}"
                    class="transition-colors {{ $product->is_active ? '' : 'opacity-50' }}"
                    style="border-bottom:1px solid rgba(255,255,255,.04)"
                    onmouseover="this.style.background='rgba(255,255,255,.025)'"
                    onmouseout="this.style.background=''">

                    {{-- Code --}}
                    <td class="px-4 py-3.5">
                        <span class="font-mono text-xs px-2 py-0.5 rounded"
                              style="background:rgba(139,92,246,.1);color:#a78bfa">{{ $product->code }}</span>
                    </td>

                    {{-- Nom + image --}}
                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-3">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" loading="lazy"
                                     class="w-9 h-9 rounded-lg object-cover flex-shrink-0"
                                     style="border:1px solid rgba(255,255,255,.08)">
                            @else
                                @php
                                    $c  = $product->category?->color ?? '#8b5cf6';
                                    $ini = collect(explode(' ', $product->name))->filter()->take(2)
                                           ->map(fn($w) => mb_substr($w, 0, 1))->join('');
                                @endphp
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-[10px] font-bold flex-shrink-0"
                                     style="background:{{ $c }}22;color:{{ $c }};border:1px solid {{ $c }}33">{{ mb_strtoupper($ini) }}</div>
                            @endif
                            <div>
                                <div class="font-medium" style="color:#e0e0ee">{{ $product->name }}</div>
                                @if ($product->isLowStock())
                                    <div class="flex items-center gap-1 text-xs font-medium" style="color:#f87171">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        Stock bas
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Catégorie --}}
                    <td class="px-4 py-3.5">
                        @if ($product->category)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold text-white"
                                  style="background-color:{{ $product->category->color }}">{{ $product->category->name }}</span>
                        @else
                            <span style="color:#3a3a55">—</span>
                        @endif
                    </td>

                    {{-- P. Achat --}}
                    <td class="px-4 py-3.5 text-right tabular-nums text-sm" style="color:#88889a">
                        {{ number_format($product->purchase_price, 0, ',', ' ') }}
                    </td>

                    {{-- P. Vente --}}
                    <td class="px-4 py-3.5 text-right tabular-nums text-sm font-semibold" style="color:#e0e0ee">
                        {{ number_format($product->selling_price, 0, ',', ' ') }}
                    </td>

                    {{-- Marge --}}
                    <td class="px-4 py-3.5 text-right tabular-nums text-sm">
                        <div class="{{ $product->margin >= 0 ? 'text-emerald-400' : 'text-red-400' }} font-semibold">
                            {{ number_format($product->margin, 0, ',', ' ') }}
                        </div>
                        <div class="text-xs" style="color:#545470">{{ number_format($product->margin_rate, 1) }}%</div>
                    </td>

                    {{-- Stock --}}
                    <td class="px-4 py-3.5 text-right tabular-nums">
                        <div class="text-sm font-bold {{ $product->isLowStock() ? 'text-red-400' : '' }}"
                             style="{{ $product->isLowStock() ? '' : 'color:#e0e0ee' }}">
                            {{ $product->stock_quantity }}
                        </div>
                        <div class="text-xs" style="color:#3a3a55">min {{ $product->min_stock }}</div>
                    </td>

                    {{-- Unité --}}
                    <td class="px-4 py-3.5 text-xs capitalize" style="color:#545470">{{ $product->unit->value }}</td>

                    {{-- Statut toggle --}}
                    <td class="px-4 py-3.5 text-center">
                        <button wire:click="toggleActive({{ $product->id }})"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition-all"
                                style="{{ $product->is_active
                                    ? 'background:rgba(52,211,153,.12);color:#34d399'
                                    : 'background:rgba(255,255,255,.05);color:#545470' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $product->is_active ? 'bg-emerald-400' : 'bg-night-600' }}"></span>
                            {{ $product->is_active ? 'Actif' : 'Inactif' }}
                        </button>
                    </td>

                    {{-- Actions --}}
                    <td class="px-4 py-3.5 text-right whitespace-nowrap">
                        <div class="flex items-center justify-end gap-1">
                            @can('edit-products')
                            <button wire:click="openEdit({{ $product->id }})"
                                    class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                                    style="color:#8b5cf6;background:rgba(139,92,246,.08)"
                                    onmouseover="this.style.background='rgba(139,92,246,.18)'"
                                    onmouseout="this.style.background='rgba(139,92,246,.08)'">
                                Modifier
                            </button>
                            @endcan
                            @can('delete-products')
                            <button wire:click="confirmDelete({{ $product->id }})"
                                    class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                                    style="color:#f87171;background:rgba(239,68,68,.06)"
                                    onmouseover="this.style.background='rgba(239,68,68,.14)'"
                                    onmouseout="this.style.background='rgba(239,68,68,.06)'">
                                Suppr.
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-4 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background:rgba(255,255,255,.04)">
                                <svg class="h-6 w-6" style="color:#3a3a55" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                </svg>
                            </div>
                            <p class="text-sm" style="color:#3a3a55">Aucun produit trouvé.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="px-5 py-3 border-t border-white/5">
        {{ $products->links() }}
    </div>

    {{-- ══════════════════════ MODAL PRODUIT ══════════════════════ --}}
    @if ($showModal)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4"
         style="background:rgba(0,0,0,.75);backdrop-filter:blur(6px)">
        <div class="w-full max-w-2xl flex flex-col rounded-2xl shadow-2xl"
             style="background:#08080f;border:1px solid rgba(255,255,255,.07);max-height:92vh">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 flex-shrink-0"
                 style="border-bottom:1px solid rgba(255,255,255,.06)">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg"
                          style="background:rgba(139,92,246,.15)">
                        <svg class="h-4 w-4" style="color:#8b5cf6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                        </svg>
                    </span>
                    <h3 class="text-base font-bold" style="color:#e0e0ee">
                        {{ $editingId ? 'Modifier le produit' : 'Nouveau produit' }}
                    </h3>
                </div>
                <button wire:click="closeModal" class="p-1.5 rounded-lg transition-colors"
                        style="color:#545470" onmouseover="this.style.color='#88889a'" onmouseout="this.style.color='#545470'">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body scrollable --}}
            <form wire:submit="save" class="flex flex-col flex-1 min-h-0">
                <div class="overflow-y-auto flex-1 p-6 space-y-5">

                    {{-- Section Identité --}}
                    <div class="rounded-xl overflow-hidden" style="background:#0d0d18;border:1px solid rgba(255,255,255,.06)">
                        <div class="flex items-center gap-2.5 px-4 py-3" style="background:#161625;border-bottom:1px solid rgba(255,255,255,.06)">
                            <svg class="h-3.5 w-3.5" style="color:#8b5cf6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <span class="text-xs font-semibold uppercase tracking-wider" style="color:#88889a">Identité</span>
                        </div>
                        <div class="p-4 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#545470">Code *</label>
                                <input wire:model="code" type="text"
                                       class="block w-full rounded-lg px-3 py-2.5 text-sm font-mono uppercase focus:outline-none focus:ring-1 focus:ring-neon-500/30 transition-colors"
                                       style="background:#08080f;border:1px solid rgba(255,255,255,.08);color:#e0e0ee">
                                @error('code') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#545470">Unité *</label>
                                <select wire:model="unit"
                                        class="block w-full rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-neon-500/30 transition-colors"
                                        style="background:#08080f;border:1px solid rgba(255,255,255,.08);color:#e0e0ee">
                                    @foreach ($units as $u)
                                        <option value="{{ $u['value'] }}">{{ $u['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#545470">Désignation *</label>
                                <input wire:model="name" type="text"
                                       class="block w-full rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-neon-500/30 transition-colors"
                                       style="background:#08080f;border:1px solid rgba(255,255,255,.08);color:#e0e0ee">
                                @error('name') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#545470">Catégorie</label>
                                <select wire:model="category_id"
                                        class="block w-full rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-neon-500/30 transition-colors"
                                        style="background:#08080f;border:1px solid rgba(255,255,255,.08);color:#e0e0ee">
                                    <option value="">— Sans catégorie —</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Section Tarification --}}
                    <div class="rounded-xl overflow-hidden" style="background:#0d0d18;border:1px solid rgba(255,255,255,.06)">
                        <div class="flex items-center gap-2.5 px-4 py-3" style="background:#161625;border-bottom:1px solid rgba(255,255,255,.06)">
                            <svg class="h-3.5 w-3.5" style="color:#d4af37" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-xs font-semibold uppercase tracking-wider" style="color:#88889a">Tarification</span>
                        </div>
                        <div class="p-4 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#545470">Prix d'achat *</label>
                                    <input wire:model.blur="purchase_price" type="number" step="1" min="0"
                                           class="block w-full rounded-lg px-3 py-2.5 text-sm tabular-nums focus:outline-none focus:ring-1 focus:ring-neon-500/30 transition-colors"
                                           style="background:#08080f;border:1px solid rgba(255,255,255,.08);color:#e0e0ee">
                                    @error('purchase_price') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#545470">Prix de vente *</label>
                                    <input wire:model.blur="selling_price" type="number" step="1" min="0"
                                           class="block w-full rounded-lg px-3 py-2.5 text-sm tabular-nums focus:outline-none focus:ring-1 focus:ring-neon-500/30 transition-colors"
                                           style="background:#08080f;border:1px solid rgba(255,255,255,.08);color:#e0e0ee">
                                    @error('selling_price') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Aperçu marge (mis à jour au blur) --}}
                            <div class="grid grid-cols-3 gap-3 p-3 rounded-xl" style="background:rgba(5,5,12,.7);border:1px solid rgba(255,255,255,.05)">
                                <div class="text-center">
                                    <div class="text-xs mb-1" style="color:#3a3a55">Marge</div>
                                    <div class="text-sm font-bold {{ $previewMargin >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                        {{ number_format($previewMargin, 0, ',', ' ') }} <span class="text-xs font-normal" style="color:#545470">FCFA</span>
                                    </div>
                                </div>
                                <div class="text-center" style="border-left:1px solid rgba(255,255,255,.05);border-right:1px solid rgba(255,255,255,.05)">
                                    <div class="text-xs mb-1" style="color:#3a3a55">Taux marge</div>
                                    <div class="text-sm font-bold" style="color:#e0e0ee">{{ number_format($previewMarginRate, 1) }}%</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-xs mb-1" style="color:#3a3a55">Taux marque</div>
                                    <div class="text-sm font-bold" style="color:#e0e0ee">{{ number_format($previewMarkupRate, 1) }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section Stock & Options --}}
                    <div class="rounded-xl overflow-hidden" style="background:#0d0d18;border:1px solid rgba(255,255,255,.06)">
                        <div class="flex items-center gap-2.5 px-4 py-3" style="background:#161625;border-bottom:1px solid rgba(255,255,255,.06)">
                            <svg class="h-3.5 w-3.5" style="color:#34d399" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span class="text-xs font-semibold uppercase tracking-wider" style="color:#88889a">Stock &amp; Options</span>
                        </div>
                        <div class="p-4 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#545470">Stock minimum</label>
                                <input wire:model="min_stock" type="number" min="0"
                                       class="block w-full rounded-lg px-3 py-2.5 text-sm tabular-nums focus:outline-none focus:ring-1 focus:ring-neon-500/30 transition-colors"
                                       style="background:#08080f;border:1px solid rgba(255,255,255,.08);color:#e0e0ee">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#545470">Image</label>
                                <input wire:model="image" type="file" accept="image/*"
                                       class="block w-full text-sm rounded-lg px-3 py-2"
                                       style="color:#88889a;background:#08080f;border:1px solid rgba(255,255,255,.08)">
                                @error('image') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#545470">Notes</label>
                                <textarea wire:model="notes" rows="2"
                                          class="block w-full rounded-lg px-3 py-2.5 text-sm resize-none focus:outline-none focus:ring-1 focus:ring-neon-500/30 transition-colors"
                                          style="background:#08080f;border:1px solid rgba(255,255,255,.08);color:#e0e0ee"></textarea>
                            </div>
                            <div class="col-span-2">
                                <label class="flex items-center gap-3 cursor-pointer select-none">
                                    <div class="relative">
                                        <input wire:model="is_active" type="checkbox" class="sr-only peer" id="prod_active">
                                        <div class="w-10 h-5 rounded-full transition-colors peer-checked:bg-neon-600"
                                             style="background:#1e1e30"></div>
                                        <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                                    </div>
                                    <span class="text-sm" style="color:#88889a">Produit actif (visible au POS)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-3 px-6 py-4 flex-shrink-0"
                     style="border-top:1px solid rgba(255,255,255,.06);background:#08080f">
                    <button type="button" wire:click="closeModal"
                            class="px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors"
                            style="color:#88889a;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08)"
                            onmouseover="this.style.background='rgba(255,255,255,.08)'"
                            onmouseout="this.style.background='rgba(255,255,255,.05)'">
                        Annuler
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-bold text-white disabled:opacity-50 transition-all"
                            style="background:linear-gradient(135deg,#5b21b6,#7c3aed);box-shadow:0 4px 16px rgba(109,40,217,.3)">
                        <svg wire:loading.remove class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span wire:loading.remove>Enregistrer</span>
                        <span wire:loading>Enregistrement…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ══════════════════════ MODAL SUPPRESSION ══════════════════════ --}}
    @if ($showDelete)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4"
         style="background:rgba(0,0,0,.75);backdrop-filter:blur(6px)">
        <div class="w-full max-w-sm rounded-2xl p-6 text-center shadow-2xl"
             style="background:#08080f;border:1px solid rgba(239,68,68,.2)">
            <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4"
                 style="background:rgba(239,68,68,.1)">
                <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <h3 class="text-base font-bold mb-1" style="color:#e0e0ee">Supprimer ce produit ?</h3>
            <p class="text-sm mb-6" style="color:#545470">Cette action est irréversible.</p>
            <div class="flex gap-3">
                <button wire:click="$set('showDelete', false)"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition-colors"
                        style="color:#88889a;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08)"
                        onmouseover="this.style.background='rgba(255,255,255,.08)'"
                        onmouseout="this.style.background='rgba(255,255,255,.05)'">
                    Annuler
                </button>
                <button wire:click="delete"
                        class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition-all active:scale-95"
                        style="background:linear-gradient(135deg,#dc2626,#ef4444)">
                    Supprimer
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
