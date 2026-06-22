<div>

    {{-- ── KPI Stats ─────────────────────────────────────────────────────────── --}}
    @php
        $totalValue  = $stockProducts->sum(fn($p) => $p->stock_quantity * ($p->purchase_price ?? 0));
        $cntOk       = $stockProducts->filter(fn($p) => $p->stock_quantity > 0 && ($p->min_stock == 0 || $p->stock_quantity > $p->min_stock))->count();
        $cntLow      = $stockProducts->filter(fn($p) => $p->stock_quantity > 0 && $p->min_stock > 0 && $p->stock_quantity <= $p->min_stock)->count();
        $cntEmpty    = $stockProducts->filter(fn($p) => $p->stock_quantity <= 0)->count();
    @endphp
    <div class="flex flex-wrap gap-3 px-5 pt-4 pb-3 border-b border-white/5">
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold"
             style="background:rgba(52,211,153,.1);color:#34d399">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            {{ $cntOk }} en stock OK
        </div>
        @if ($cntLow > 0)
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold"
             style="background:rgba(251,191,36,.1);color:#fbbf24">
            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $cntLow }} sous seuil
        </div>
        @endif
        @if ($cntEmpty > 0)
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold"
             style="background:rgba(239,68,68,.1);color:#f87171">
            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
            {{ $cntEmpty }} épuisé{{ $cntEmpty > 1 ? 's' : '' }}
        </div>
        @endif
        <div class="ml-auto flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold"
             style="background:rgba(212,175,55,.08);color:#d4af37">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Valeur stock : {{ number_format($totalValue, 0, ',', ' ') }} FCFA
        </div>
    </div>

    {{-- ── Onglets ───────────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-0 px-5 border-b border-white/5" style="background:rgba(5,5,12,.3)">
        @foreach (['stock' => 'État du stock', 'journal' => 'Journal des mouvements'] as $key => $label)
        <button wire:click="$set('tab','{{ $key }}')"
                class="px-5 py-3 text-sm font-semibold border-b-2 transition-colors -mb-px"
                style="{{ $tab === $key
                    ? 'border-color:#7c3aed;color:#a78bfa'
                    : 'border-color:transparent;color:#545470' }}"
                onmouseover="{{ $tab !== $key ? "this.style.color='#88889a'" : '' }}"
                onmouseout="{{ $tab !== $key ? "this.style.color='#545470'" : '' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- ══════════════════ ONGLET ÉTAT DU STOCK ══════════════════ --}}
    @if ($tab === 'stock')

    {{-- Barre filtres --}}
    <div class="flex flex-wrap items-center gap-2 px-5 py-3 border-b border-white/5" style="background:rgba(5,5,12,.4)">
        <div class="relative flex-1 min-w-[180px] max-w-xs">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 pointer-events-none" style="color:#545470"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="stockSearch" type="text" placeholder="Rechercher…"
                   class="w-full pl-9 pr-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-neon-500/30 transition-colors"
                   style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#e0e0ee">
        </div>

        <div class="flex gap-1">
            @foreach (['' => 'Tous', 'ok' => 'OK', 'low' => 'Sous seuil', 'empty' => 'Épuisés'] as $val => $lbl)
            @php $active = $stockFilter === $val; @endphp
            <button wire:click="$set('stockFilter','{{ $val }}')"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all"
                    style="{{ $active
                        ? 'background:rgba(124,58,237,.25);color:#a78bfa;border:1px solid rgba(124,58,237,.4)'
                        : 'background:rgba(255,255,255,.04);color:#545470;border:1px solid rgba(255,255,255,.08)' }}"
                    onmouseover="{{ !$active ? "this.style.color='#88889a'" : '' }}"
                    onmouseout="{{ !$active ? "this.style.color='#545470'" : '' }}">
                {{ $lbl }}
            </button>
            @endforeach
        </div>

        @can('adjust-stock')
        <button wire:click="openForm"
                class="ml-auto flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold text-white transition-all active:scale-95"
                style="background:linear-gradient(135deg,#5b21b6,#7c3aed);box-shadow:0 4px 16px rgba(109,40,217,.3)">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Mouvement manuel
        </button>
        @endcan
    </div>

    {{-- Tableau état du stock --}}
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr style="background:rgba(22,22,37,.9);border-bottom:1px solid rgba(255,255,255,.06)">
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color:#545470">Produit</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color:#545470">Catégorie</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">Stock actuel</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">Seuil min</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider" style="color:#545470">Statut</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">Valeur</th>
                    @can('adjust-stock')
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider" style="color:#545470">Ajuster</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse ($stockProducts as $p)
                @php
                    $isEmpty  = $p->stock_quantity <= 0;
                    $isLow    = !$isEmpty && $p->min_stock > 0 && $p->stock_quantity <= $p->min_stock;
                    $isOk     = !$isEmpty && !$isLow;
                    $stockVal = $p->stock_quantity * ($p->purchase_price ?? 0);
                    $catColor = $p->category?->color ?? '#6366f1';
                @endphp
                <tr wire:key="sp-{{ $p->id }}"
                    class="transition-colors"
                    style="border-bottom:1px solid rgba(255,255,255,.04)"
                    onmouseover="this.style.background='rgba(255,255,255,.025)'"
                    onmouseout="this.style.background=''">

                    <td class="px-4 py-3.5 font-medium" style="color:#e0e0ee">{{ $p->name }}</td>

                    <td class="px-4 py-3.5">
                        @if ($p->category)
                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full"
                                  style="background:{{ $catColor }}22;color:{{ $catColor }}">
                                {{ $p->category->name }}
                            </span>
                        @else
                            <span style="color:#3a3a55">—</span>
                        @endif
                    </td>

                    <td class="px-4 py-3.5 text-right">
                        <span class="text-sm font-bold tabular-nums
                            {{ $isEmpty ? 'text-red-400' : ($isLow ? 'text-amber-400' : 'text-emerald-400') }}">
                            {{ number_format($p->stock_quantity, 2) }}
                        </span>
                        <span class="text-xs ml-0.5" style="color:#3a3a55">{{ $p->unit->value }}</span>
                    </td>

                    <td class="px-4 py-3.5 text-right tabular-nums text-sm" style="color:#545470">
                        {{ $p->min_stock > 0 ? number_format($p->min_stock, 2) : '—' }}
                    </td>

                    <td class="px-4 py-3.5 text-center">
                        @if ($isEmpty)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                  style="background:rgba(239,68,68,.12);color:#f87171">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Épuisé
                            </span>
                        @elseif ($isLow)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                  style="background:rgba(251,191,36,.1);color:#fbbf24">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Sous seuil
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                  style="background:rgba(52,211,153,.1);color:#34d399">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>OK
                            </span>
                        @endif
                    </td>

                    <td class="px-4 py-3.5 text-right tabular-nums text-sm" style="color:#88889a">
                        @if ($p->purchase_price)
                            {{ number_format($stockVal, 0, ',', ' ') }} <span class="text-xs" style="color:#3a3a55">FCFA</span>
                        @else
                            <span style="color:#3a3a55">—</span>
                        @endif
                    </td>

                    @can('adjust-stock')
                    <td class="px-4 py-3.5 text-center">
                        <div class="inline-flex gap-1">
                            <button wire:click="quickAdjust({{ $p->id }}, 'manual_in')"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-sm font-bold transition-all"
                                    style="background:rgba(52,211,153,.1);color:#34d399"
                                    onmouseover="this.style.background='rgba(52,211,153,.2)'"
                                    onmouseout="this.style.background='rgba(52,211,153,.1)'"
                                    title="Entrée stock">+</button>
                            <button wire:click="quickAdjust({{ $p->id }}, 'manual_out')"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-sm font-bold transition-all"
                                    style="background:rgba(239,68,68,.1);color:#f87171"
                                    onmouseover="this.style.background='rgba(239,68,68,.2)'"
                                    onmouseout="this.style.background='rgba(239,68,68,.1)'"
                                    title="Sortie stock">−</button>
                        </div>
                    </td>
                    @endcan
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background:rgba(255,255,255,.04)">
                                <svg class="h-6 w-6" style="color:#3a3a55" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <p class="text-sm" style="color:#3a3a55">Aucun produit trouvé.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if ($stockProducts->isNotEmpty())
            <tfoot style="background:rgba(22,22,37,.6);border-top:1px solid rgba(255,255,255,.06)">
                <tr>
                    <td colspan="5" class="px-4 py-3 text-xs" style="color:#3a3a55">
                        {{ $stockProducts->count() }} produit{{ $stockProducts->count() !== 1 ? 's' : '' }}
                    </td>
                    <td class="px-4 py-3 text-right text-sm font-bold tabular-nums" style="color:#d4af37">
                        {{ number_format($totalValue, 0, ',', ' ') }} FCFA
                    </td>
                    @can('adjust-stock')<td></td>@endcan
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- ══════════════════ ONGLET JOURNAL ══════════════════ --}}
    @else

    {{-- Filtres journal --}}
    <div class="flex flex-wrap items-center gap-2 px-5 py-3 border-b border-white/5" style="background:rgba(5,5,12,.4)">
        <div class="relative flex-1 min-w-[180px] max-w-xs">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 pointer-events-none" style="color:#545470"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Produit…"
                   class="w-full pl-9 pr-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-neon-500/30 transition-colors"
                   style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#e0e0ee">
        </div>

        <select wire:model.live="filterType"
                class="py-2 pl-3 pr-8 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-neon-500/30"
                style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#88889a">
            <option value="">Tous types</option>
            @foreach ($types as $t)
                <option value="{{ $t['value'] }}">{{ $t['label'] }}</option>
            @endforeach
        </select>

        <div class="flex items-center gap-2">
            <input wire:model.live="dateFrom" type="date"
                   class="py-2 px-3 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-neon-500/30"
                   style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#88889a">
            <span style="color:#3a3a55">→</span>
            <input wire:model.live="dateTo" type="date"
                   class="py-2 px-3 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-neon-500/30"
                   style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#88889a">
        </div>

        @can('adjust-stock')
        <button wire:click="openForm"
                class="ml-auto flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold text-white transition-all active:scale-95"
                style="background:linear-gradient(135deg,#5b21b6,#7c3aed);box-shadow:0 4px 16px rgba(109,40,217,.3)">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Mouvement manuel
        </button>
        @endcan
    </div>

    {{-- Table journal --}}
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr style="background:rgba(22,22,37,.9);border-bottom:1px solid rgba(255,255,255,.06)">
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color:#545470">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color:#545470">Produit</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color:#545470">Type</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">Avant</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">Δ</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">Après</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color:#545470">Notes</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color:#545470">Opérateur</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movements as $m)
                @php $delta = $m->quantity_after - $m->quantity_before; @endphp
                <tr wire:key="mv-{{ $m->id }}"
                    class="transition-colors"
                    style="border-bottom:1px solid rgba(255,255,255,.04)"
                    onmouseover="this.style.background='rgba(255,255,255,.02)'"
                    onmouseout="this.style.background=''">

                    <td class="px-4 py-3.5 whitespace-nowrap">
                        <div class="text-xs font-mono tabular-nums" style="color:#545470">{{ $m->created_at->format('d/m/Y') }}</div>
                        <div class="text-xs tabular-nums" style="color:#3a3a55">{{ $m->created_at->format('H:i') }}</div>
                    </td>

                    <td class="px-4 py-3.5 font-medium" style="color:#e0e0ee">
                        {{ $m->product?->name ?? '—' }}
                    </td>

                    <td class="px-4 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $m->type->badgeClass() }}">
                            {{ $m->type->label() }}
                        </span>
                    </td>

                    <td class="px-4 py-3.5 text-right tabular-nums text-sm" style="color:#88889a">
                        {{ number_format($m->quantity_before, 2) }}
                    </td>

                    <td class="px-4 py-3.5 text-right tabular-nums text-sm font-bold {{ $delta >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                        {{ $delta >= 0 ? '+' : '' }}{{ number_format($delta, 2) }}
                    </td>

                    <td class="px-4 py-3.5 text-right tabular-nums text-sm font-semibold" style="color:#e0e0ee">
                        {{ number_format($m->quantity_after, 2) }}
                    </td>

                    <td class="px-4 py-3.5 max-w-xs">
                        <span class="text-xs truncate block" style="color:#545470" title="{{ $m->notes }}">
                            {{ $m->notes ?? '—' }}
                        </span>
                    </td>

                    <td class="px-4 py-3.5">
                        <span class="text-xs px-2 py-0.5 rounded" style="background:rgba(255,255,255,.04);color:#88889a">
                            {{ $m->user?->name ?? 'Système' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background:rgba(255,255,255,.04)">
                                <svg class="h-6 w-6" style="color:#3a3a55" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="text-sm" style="color:#3a3a55">Aucun mouvement enregistré.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-5 py-3 border-t border-white/5">
        {{ $movements->links() }}
    </div>

    @endif

    {{-- ══════════════════ MODAL MOUVEMENT MANUEL ══════════════════ --}}
    @if ($showForm)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4"
         style="background:rgba(0,0,0,.75);backdrop-filter:blur(6px)">
        <div class="w-full max-w-md rounded-2xl shadow-2xl"
             style="background:#08080f;border:1px solid rgba(255,255,255,.07)">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4"
                 style="border-bottom:1px solid rgba(255,255,255,.06)">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg"
                          style="background:rgba(52,211,153,.12)">
                        <svg class="h-4 w-4" style="color:#34d399" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                        </svg>
                    </span>
                    <h3 class="text-base font-bold" style="color:#e0e0ee">Mouvement manuel</h3>
                </div>
                <button wire:click="$set('showForm', false)" class="p-1.5 rounded-lg transition-colors"
                        style="color:#545470" onmouseover="this.style.color='#88889a'" onmouseout="this.style.color='#545470'">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-6 space-y-4">

                {{-- Type (tabs) --}}
                <div class="flex rounded-xl overflow-hidden" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06)">
                    <button wire:click="$set('formType','manual_in')" type="button"
                            class="flex-1 flex items-center justify-center gap-2 py-2.5 text-sm font-semibold transition-all"
                            style="{{ $formType === 'manual_in'
                                ? 'background:rgba(52,211,153,.15);color:#34d399'
                                : 'color:#545470' }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Entrée
                    </button>
                    <button wire:click="$set('formType','manual_out')" type="button"
                            class="flex-1 flex items-center justify-center gap-2 py-2.5 text-sm font-semibold transition-all"
                            style="{{ $formType === 'manual_out'
                                ? 'background:rgba(239,68,68,.12);color:#f87171'
                                : 'color:#545470' }}"
                            onmouseover="{{ $formType !== 'manual_out' ? "this.style.color='#88889a'" : '' }}"
                            onmouseout="{{ $formType !== 'manual_out' ? "this.style.color='#545470'" : '' }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/>
                        </svg>
                        Sortie
                    </button>
                </div>

                {{-- Produit --}}
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#545470">Produit *</label>
                    <select wire:model="formProduct"
                            class="block w-full rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-neon-500/30"
                            style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#e0e0ee">
                        <option value="">— Sélectionner un produit —</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (stock: {{ $p->stock_quantity }})</option>
                        @endforeach
                    </select>
                    @error('formProduct') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Quantité --}}
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#545470">Quantité *</label>
                    <input wire:model="formQty" type="number" step="0.0001" min="0.0001"
                           class="block w-full rounded-lg px-3 py-2.5 text-sm tabular-nums focus:outline-none focus:ring-1 focus:ring-neon-500/30"
                           style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#e0e0ee">
                    @error('formQty') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#545470">Notes</label>
                    <input wire:model="formNotes" type="text" placeholder="Raison du mouvement…"
                           class="block w-full rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-neon-500/30"
                           style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#e0e0ee">
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex gap-3 px-6 py-4" style="border-top:1px solid rgba(255,255,255,.06);background:#08080f">
                <button wire:click="$set('showForm', false)"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition-colors"
                        style="color:#88889a;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08)"
                        onmouseover="this.style.background='rgba(255,255,255,.08)'"
                        onmouseout="this.style.background='rgba(255,255,255,.05)'">
                    Annuler
                </button>
                <button wire:click="saveMovement"
                        class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition-all active:scale-95"
                        style="{{ $formType === 'manual_out'
                            ? 'background:linear-gradient(135deg,#dc2626,#ef4444)'
                            : 'background:linear-gradient(135deg,#059669,#10b981)' }}">
                    <span wire:loading.remove wire:target="saveMovement">
                        {{ $formType === 'manual_out' ? 'Sortie stock' : 'Entrée stock' }}
                    </span>
                    <span wire:loading wire:target="saveMovement">Enregistrement…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
