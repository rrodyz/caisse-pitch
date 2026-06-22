@php
    $typeMap = [
        'purchase_in'          => ['label' => 'Achat entrant',     'hex' => '#10b981', 'bg' => 'rgba(16,185,129,.12)'],
        'sale_out'             => ['label' => 'Vente',             'hex' => '#f97316', 'bg' => 'rgba(249,115,22,.12)'],
        'loss'                 => ['label' => 'Perte',             'hex' => '#ef4444', 'bg' => 'rgba(239,68,68,.12)'],
        'break'                => ['label' => 'Casse',             'hex' => '#f43f5e', 'bg' => 'rgba(244,63,94,.12)'],
        'gift'                 => ['label' => 'Offert',            'hex' => '#8b5cf6', 'bg' => 'rgba(139,92,246,.12)'],
        'inventory_adjustment' => ['label' => 'Inventaire',        'hex' => '#3b82f6', 'bg' => 'rgba(59,130,246,.12)'],
        'manual_in'            => ['label' => 'Entrée manuelle',   'hex' => '#34d399', 'bg' => 'rgba(52,211,153,.12)'],
        'manual_out'           => ['label' => 'Sortie manuelle',   'hex' => '#f87171', 'bg' => 'rgba(248,113,113,.12)'],
    ];
@endphp

<div>

    {{-- ── KPI toujours visibles ────────────────────────────────────────────── --}}
    @if (isset($data['summary']))
    @php $s = $data['summary']; @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-0 border-b border-white/5">
        <div class="px-6 py-5 border-r border-white/5">
            <div class="flex items-center gap-2 mb-2">
                <svg class="h-4 w-4" style="color:#d4af37" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-xs font-semibold uppercase tracking-wider" style="color:#545470">Valeur totale</span>
            </div>
            <div class="text-2xl font-black tabular-nums" style="background:linear-gradient(135deg,#e8c840,#d4af37);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">
                {{ number_format($s['total_value'], 0, ',', ' ') }}
            </div>
            <div class="text-xs mt-0.5" style="color:#3a3a55">FCFA</div>
        </div>
        <div class="px-6 py-5 border-r border-white/5">
            <div class="flex items-center gap-2 mb-2">
                <svg class="h-4 w-4" style="color:#8b5cf6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
                <span class="text-xs font-semibold uppercase tracking-wider" style="color:#545470">Références</span>
            </div>
            <div class="text-2xl font-black" style="color:#a78bfa">{{ $s['total_products'] }}</div>
            <div class="text-xs mt-0.5" style="color:#3a3a55">produits actifs</div>
        </div>
        <div class="px-6 py-5 border-r border-white/5">
            <div class="flex items-center gap-2 mb-2">
                <svg class="h-4 w-4" style="color:#fbbf24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <span class="text-xs font-semibold uppercase tracking-wider" style="color:#545470">Stock bas</span>
            </div>
            <div class="text-2xl font-black" style="color:#fbbf24">{{ $s['low_stock_count'] }}</div>
            <div class="text-xs mt-0.5" style="color:#3a3a55">sous le seuil</div>
        </div>
        <div class="px-6 py-5">
            <div class="flex items-center gap-2 mb-2">
                <svg class="h-4 w-4" style="color:#f87171" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                <span class="text-xs font-semibold uppercase tracking-wider" style="color:#545470">Ruptures</span>
            </div>
            <div class="text-2xl font-black" style="color:#f87171">{{ $s['out_stock_count'] }}</div>
            <div class="text-xs mt-0.5" style="color:#3a3a55">épuisés</div>
        </div>
    </div>
    @endif

    {{-- ── Onglets + exports ────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between px-5 border-b border-white/5" style="background:rgba(5,5,12,.3)">
        <div class="flex gap-0">
            @foreach (['valuation' => 'Valorisation', 'movements' => 'Mouvements', 'alerts' => 'Alertes'] as $tab => $label)
            @php $active = $view === $tab; @endphp
            <button wire:click="$set('view','{{ $tab }}')"
                    class="px-5 py-3 text-sm font-semibold border-b-2 transition-colors -mb-px flex items-center gap-2"
                    style="{{ $active
                        ? 'border-color:#7c3aed;color:#a78bfa'
                        : 'border-color:transparent;color:#545470' }}"
                    onmouseover="{{ !$active ? "this.style.color='#88889a'" : '' }}"
                    onmouseout="{{ !$active ? "this.style.color='#545470'" : '' }}">
                {{ $label }}
                @if ($tab === 'alerts' && isset($data['rows']) && $data['rows']->count() > 0 && $view !== 'alerts')
                    <span class="px-1.5 py-0.5 rounded-full text-xs font-bold leading-none"
                          style="background:rgba(239,68,68,.2);color:#f87171">{{ $data['rows']->count() }}</span>
                @endif
            </button>
            @endforeach
        </div>

        @can('export-reports')
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.stock.pdf', array_filter([
                    'view'       => $view,
                    'search'     => $search,
                    'categoryId' => $categoryId,
                    'sortBy'     => $sortBy,
                    'dateFrom'   => $dateFrom,
                    'dateTo'     => $dateTo,
                    'filterType' => $filterType,
                ])) }}" target="_blank"
               class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
               style="background:rgba(220,38,38,.12);color:#f87171;border:1px solid rgba(220,38,38,.2)"
               onmouseover="this.style.background='rgba(220,38,38,.22)'"
               onmouseout="this.style.background='rgba(220,38,38,.12)'">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                PDF
            </a>
            <button wire:click="export" wire:loading.attr="disabled"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all disabled:opacity-50"
                    style="background:rgba(16,185,129,.12);color:#34d399;border:1px solid rgba(16,185,129,.2)"
                    onmouseover="this.style.background='rgba(16,185,129,.22)'"
                    onmouseout="this.style.background='rgba(16,185,129,.12)'">
                <svg wire:loading.remove wire:target="export" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span wire:loading.remove wire:target="export">Excel</span>
                <span wire:loading wire:target="export">…</span>
            </button>
        </div>
        @endcan
    </div>

    {{-- ══════════════ VALORISATION ══════════════ --}}
    @if ($view === 'valuation' && isset($data['rows']))

    {{-- Filtres --}}
    <div class="flex flex-wrap items-center gap-2 px-5 py-3 border-b border-white/5" style="background:rgba(5,5,12,.4)">
        <div class="relative flex-1 min-w-[160px] max-w-xs">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 pointer-events-none" style="color:#545470"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher…"
                   class="w-full pl-9 pr-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-neon-500/30"
                   style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#e0e0ee">
        </div>
        <select wire:model.live="categoryId"
                class="py-2 pl-3 pr-8 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-neon-500/30"
                style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#88889a">
            <option value="">Toutes catégories</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="sortBy"
                class="py-2 pl-3 pr-8 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-neon-500/30"
                style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#88889a">
            <option value="value">Trier : Valeur</option>
            <option value="qty">Trier : Quantité</option>
            <option value="name">Trier : Nom</option>
        </select>
        <span class="text-xs ml-auto" style="color:#3a3a55">{{ $data['rows']->count() }} produit{{ $data['rows']->count() !== 1 ? 's' : '' }}</span>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr style="background:rgba(22,22,37,.9);border-bottom:1px solid rgba(255,255,255,.06)">
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color:#545470">Produit</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color:#545470">Catégorie</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">Stock</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">Mini</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">Px achat</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">Valeur stock</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider" style="color:#545470">Statut</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['rows'] as $row)
                @php
                    $isEmpty = $row->stock_quantity <= 0;
                    $isLow   = !$isEmpty && $row->stock_quantity <= $row->min_stock;
                @endphp
                <tr wire:key="v-{{ $row->id }}"
                    class="transition-colors"
                    style="border-bottom:1px solid rgba(255,255,255,.04)"
                    onmouseover="this.style.background='rgba(255,255,255,.02)'"
                    onmouseout="this.style.background=''">
                    <td class="px-4 py-3.5 font-medium" style="color:#e0e0ee">{{ $row->name }}</td>
                    <td class="px-4 py-3.5 text-xs" style="color:#545470">{{ $row->category_name ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-right tabular-nums font-bold {{ $isEmpty ? 'text-red-400' : ($isLow ? 'text-amber-400' : 'text-emerald-400') }}">
                        {{ number_format($row->stock_quantity, 2) }}
                        <span class="text-xs font-normal" style="color:#3a3a55">{{ $row->unit }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right tabular-nums text-xs" style="color:#3a3a55">{{ number_format($row->min_stock, 2) }}</td>
                    <td class="px-4 py-3.5 text-right tabular-nums text-xs" style="color:#545470">{{ number_format($row->purchase_price, 0, ',', ' ') }}</td>
                    <td class="px-4 py-3.5 text-right tabular-nums text-sm font-bold" style="color:#e0e0ee">
                        {{ number_format($row->stock_value, 0, ',', ' ') }}
                        <span class="text-xs font-normal" style="color:#3a3a55">FCFA</span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if ($isEmpty)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                  style="background:rgba(239,68,68,.12);color:#f87171">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Rupture
                            </span>
                        @elseif ($isLow)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                  style="background:rgba(251,191,36,.1);color:#fbbf24">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Stock bas
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                  style="background:rgba(52,211,153,.1);color:#34d399">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>OK
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-16 text-center">
                        <p class="text-sm" style="color:#3a3a55">Aucun produit trouvé.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if ($data['rows']->isNotEmpty())
            <tfoot style="background:rgba(22,22,37,.6);border-top:1px solid rgba(255,255,255,.06)">
                <tr>
                    <td class="px-4 py-3 text-xs font-bold uppercase tracking-wider" style="color:#545470" colspan="5">Total</td>
                    <td class="px-4 py-3 text-right tabular-nums text-sm font-black" style="color:#d4af37">
                        {{ number_format($data['summary']['total_value'], 0, ',', ' ') }} <span class="text-xs font-normal" style="color:#3a3a55">FCFA</span>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- ══════════════ MOUVEMENTS ══════════════ --}}
    @elseif ($view === 'movements' && isset($data['rows']))

    {{-- Filtres --}}
    <div class="flex flex-wrap items-center gap-2 px-5 py-3 border-b border-white/5" style="background:rgba(5,5,12,.4)">
        <div class="relative flex-1 min-w-[160px] max-w-xs">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 pointer-events-none" style="color:#545470"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Produit…"
                   class="w-full pl-9 pr-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-neon-500/30"
                   style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#e0e0ee">
        </div>
        <select wire:model.live="filterType"
                class="py-2 pl-3 pr-8 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-neon-500/30"
                style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#88889a">
            <option value="">Tous types</option>
            @foreach ($typeMap as $val => $t)
                <option value="{{ $val }}">{{ $t['label'] }}</option>
            @endforeach
        </select>
        <div class="flex items-center gap-2">
            <input wire:model.live="dateFrom" type="date"
                   class="py-2 px-3 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-neon-500/30"
                   style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#88889a">
            <span style="color:#3a3a55">→</span>
            <input wire:model.live="dateTo" type="date"
                   class="py-2 px-3 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-neon-500/30"
                   style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#88889a">
        </div>
    </div>

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
                @forelse ($data['rows'] as $row)
                @php
                    $delta = $row->quantity_after - $row->quantity_before;
                    $tm    = $typeMap[$row->type] ?? ['label' => $row->type, 'hex' => '#88889a', 'bg' => 'rgba(136,136,154,.1)'];
                @endphp
                <tr wire:key="m-{{ $row->id }}"
                    class="transition-colors"
                    style="border-bottom:1px solid rgba(255,255,255,.04)"
                    onmouseover="this.style.background='rgba(255,255,255,.02)'"
                    onmouseout="this.style.background=''">
                    <td class="px-4 py-3.5 whitespace-nowrap">
                        <div class="text-xs font-mono tabular-nums" style="color:#545470">{{ \Carbon\Carbon::parse($row->created_at)->format('d/m/Y') }}</div>
                        <div class="text-xs tabular-nums" style="color:#3a3a55">{{ \Carbon\Carbon::parse($row->created_at)->format('H:i') }}</div>
                    </td>
                    <td class="px-4 py-3.5 font-medium" style="color:#e0e0ee">{{ $row->product_name }}</td>
                    <td class="px-4 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                              style="background:{{ $tm['bg'] }};color:{{ $tm['hex'] }}">
                            {{ $tm['label'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-right tabular-nums text-xs" style="color:#545470">{{ number_format($row->quantity_before, 3) }}</td>
                    <td class="px-4 py-3.5 text-right tabular-nums text-sm font-bold {{ $delta >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                        {{ $delta >= 0 ? '+' : '' }}{{ number_format($delta, 3) }}
                    </td>
                    <td class="px-4 py-3.5 text-right tabular-nums text-sm font-semibold" style="color:#e0e0ee">{{ number_format($row->quantity_after, 3) }}</td>
                    <td class="px-4 py-3.5 max-w-xs">
                        <span class="text-xs truncate block" style="color:#545470" title="{{ $row->notes }}">{{ $row->notes ?? '—' }}</span>
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="text-xs px-2 py-0.5 rounded" style="background:rgba(255,255,255,.04);color:#88889a">
                            {{ $row->user_name ?? 'Système' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-16 text-center">
                        <p class="text-sm" style="color:#3a3a55">Aucun mouvement.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-white/5">{{ $data['rows']->links() }}</div>

    {{-- ══════════════ ALERTES ══════════════ --}}
    @elseif ($view === 'alerts' && isset($data['rows']))

    @if ($data['rows']->isEmpty())
    <div class="flex flex-col items-center gap-4 py-24">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center" style="background:rgba(52,211,153,.1)">
            <svg class="h-8 w-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="text-center">
            <p class="text-base font-bold text-emerald-400">Aucune alerte</p>
            <p class="text-sm mt-1" style="color:#3a3a55">Tous les niveaux de stock sont satisfaisants.</p>
        </div>
    </div>
    @else

    {{-- Banner alerte --}}
    <div class="flex items-center gap-3 mx-5 mt-4 mb-3 px-4 py-3 rounded-xl"
         style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2)">
        <svg class="h-5 w-5 flex-shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <span class="text-sm font-semibold text-red-300">
            {{ $data['rows']->count() }} produit{{ $data['rows']->count() !== 1 ? 's' : '' }} nécessite{{ $data['rows']->count() === 1 ? '' : 'nt' }} un réapprovisionnement.
        </span>
    </div>

    <div class="overflow-x-auto px-0">
        <table class="min-w-full text-sm">
            <thead>
                <tr style="background:rgba(22,22,37,.9);border-bottom:1px solid rgba(255,255,255,.06)">
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color:#545470">Produit</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color:#545470">Catégorie</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">Stock actuel</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">Seuil mini</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#f87171">Manquant</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider" style="color:#545470">Urgence</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['rows'] as $row)
                @php $rupture = $row->stock_quantity <= 0; @endphp
                <tr wire:key="a-{{ $row->id }}"
                    class="transition-colors"
                    style="border-bottom:1px solid rgba(255,255,255,.04)"
                    onmouseover="this.style.background='rgba(255,255,255,.02)'"
                    onmouseout="this.style.background=''">
                    <td class="px-4 py-3.5 font-medium" style="color:#e0e0ee">{{ $row->name }}</td>
                    <td class="px-4 py-3.5 text-xs" style="color:#545470">{{ $row->category_name ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-right tabular-nums font-bold {{ $rupture ? 'text-red-400' : 'text-amber-400' }}">
                        {{ number_format($row->stock_quantity, 2) }}
                        <span class="text-xs font-normal" style="color:#3a3a55">{{ $row->unit }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right tabular-nums text-xs" style="color:#545470">{{ number_format($row->min_stock, 2) }}</td>
                    <td class="px-4 py-3.5 text-right tabular-nums font-black text-red-400">
                        {{ number_format($row->shortage, 2) }}
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if ($rupture)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold"
                                  style="background:rgba(239,68,68,.15);color:#f87171">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Rupture
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                  style="background:rgba(251,191,36,.1);color:#fbbf24">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Stock bas
                            </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @endif

</div>
