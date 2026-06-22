@php
    $modeColors = [
        'cash'         => ['hex' => '#10b981', 'bg' => 'rgba(16,185,129,.12)'],
        'card'         => ['hex' => '#3b82f6', 'bg' => 'rgba(59,130,246,.12)'],
        'mobile_money' => ['hex' => '#f59e0b', 'bg' => 'rgba(245,158,11,.12)'],
        'orange_money' => ['hex' => '#f97316', 'bg' => 'rgba(249,115,22,.12)'],
        'moov_money'   => ['hex' => '#06b6d4', 'bg' => 'rgba(6,182,212,.12)'],
        'wave'         => ['hex' => '#8b5cf6', 'bg' => 'rgba(139,92,246,.12)'],
        'credit'       => ['hex' => '#ef4444', 'bg' => 'rgba(239,68,68,.12)'],
    ];
    $avgTicket = $totals['cnt'] > 0 ? $totals['total'] / $totals['cnt'] : 0;
    $topMode   = $byMode->first();
@endphp

<div>

    {{-- ── KPI Hero ─────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-0 border-b border-white/5">
        {{-- CA Total --}}
        <div class="px-6 py-5 border-r border-white/5">
            <div class="flex items-center gap-2 mb-2">
                <svg class="h-4 w-4" style="color:#d4af37" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-xs font-semibold uppercase tracking-wider" style="color:#545470">CA Total</span>
            </div>
            <div class="text-2xl font-black tabular-nums" style="background:linear-gradient(135deg,#e8c840,#d4af37);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">
                {{ number_format($totals['total'], 0, ',', ' ') }}
            </div>
            <div class="text-xs mt-0.5" style="color:#3a3a55">FCFA</div>
        </div>
        {{-- Transactions --}}
        <div class="px-6 py-5 border-r border-white/5">
            <div class="flex items-center gap-2 mb-2">
                <svg class="h-4 w-4" style="color:#8b5cf6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="text-xs font-semibold uppercase tracking-wider" style="color:#545470">Transactions</span>
            </div>
            <div class="text-2xl font-black tabular-nums" style="color:#a78bfa">{{ number_format($totals['cnt']) }}</div>
            <div class="text-xs mt-0.5" style="color:#3a3a55">ventes</div>
        </div>
        {{-- Ticket moyen --}}
        <div class="px-6 py-5 border-r border-white/5">
            <div class="flex items-center gap-2 mb-2">
                <svg class="h-4 w-4" style="color:#60a5fa" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="text-xs font-semibold uppercase tracking-wider" style="color:#545470">Ticket moyen</span>
            </div>
            <div class="text-2xl font-black tabular-nums" style="color:#93c5fd">{{ number_format($avgTicket, 0, ',', ' ') }}</div>
            <div class="text-xs mt-0.5" style="color:#3a3a55">FCFA/vente</div>
        </div>
        {{-- Top mode --}}
        <div class="px-6 py-5">
            <div class="flex items-center gap-2 mb-2">
                <svg class="h-4 w-4" style="color:#34d399" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
                <span class="text-xs font-semibold uppercase tracking-wider" style="color:#545470">Top mode</span>
            </div>
            @if ($topMode)
                <div class="text-lg font-bold" style="color:#34d399">{{ $topMode->label }}</div>
                <div class="text-xs mt-0.5" style="color:#3a3a55">{{ number_format($topMode->cnt) }} transactions</div>
            @else
                <div class="text-sm" style="color:#3a3a55">—</div>
            @endif
        </div>
    </div>

    {{-- ── Filtres ───────────────────────────────────────────────────────────── --}}
    <div class="px-5 py-3.5 border-b border-white/5 flex flex-wrap items-center gap-3" style="background:rgba(5,5,12,.4)">

        {{-- Période chips --}}
        <div class="flex gap-1 flex-wrap">
            @foreach (['today' => "Auj.", 'week' => 'Semaine', 'month' => 'Mois', 'year' => 'Année', 'custom' => 'Perso.'] as $val => $lbl)
            @php $active = $period === $val; @endphp
            <button wire:click="$set('period','{{ $val }}')"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all"
                    style="{{ $active
                        ? 'background:rgba(124,58,237,.25);color:#a78bfa;border:1px solid rgba(124,58,237,.4)'
                        : 'background:rgba(255,255,255,.04);color:#545470;border:1px solid rgba(255,255,255,.08)' }}">
                {{ $lbl }}
            </button>
            @endforeach
        </div>

        {{-- Dates custom --}}
        @if ($period === 'custom')
        <div class="flex items-center gap-2">
            <input wire:model.live="dateFrom" type="date"
                   class="py-1.5 px-2.5 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-neon-500/30"
                   style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#88889a">
            <span style="color:#3a3a55">→</span>
            <input wire:model.live="dateTo" type="date"
                   class="py-1.5 px-2.5 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-neon-500/30"
                   style="background:#0d0d18;border:1px solid rgba(255,255,255,.08);color:#88889a">
        </div>
        @endif

        {{-- Séparateur --}}
        <div class="h-5 w-px" style="background:rgba(255,255,255,.08)"></div>

        {{-- Vue --}}
        <div class="flex gap-1">
            @foreach (['modes' => 'Par mode', 'daily' => 'Journalier'] as $val => $lbl)
            @php $active = $view === $val; @endphp
            <button wire:click="$set('view','{{ $val }}')"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all"
                    style="{{ $active
                        ? 'background:rgba(16,185,129,.15);color:#34d399;border:1px solid rgba(16,185,129,.3)'
                        : 'background:rgba(255,255,255,.04);color:#545470;border:1px solid rgba(255,255,255,.08)' }}">
                {{ $lbl }}
            </button>
            @endforeach
        </div>

        {{-- Date résumé --}}
        <span class="text-xs" style="color:#3a3a55">
            {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
        </span>

        {{-- PDF --}}
        @can('export-reports')
        <a href="{{ route('reports.payments.pdf', array_filter(['dateFrom' => $dateFrom, 'dateTo' => $dateTo])) }}"
           target="_blank"
           class="ml-auto flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-bold text-white transition-all"
           style="background:rgba(220,38,38,.2);color:#f87171;border:1px solid rgba(220,38,38,.3)"
           onmouseover="this.style.background='rgba(220,38,38,.3)'"
           onmouseout="this.style.background='rgba(220,38,38,.2)'">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            PDF
        </a>
        @endcan
    </div>

    {{-- ══════════════ VUE PAR MODE ══════════════ --}}
    @if ($view === 'modes')
    <div class="p-5 space-y-5">

        @if ($byMode->isEmpty())
        <div class="flex flex-col items-center gap-3 py-20">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center" style="background:rgba(255,255,255,.04)">
                <svg class="h-7 w-7" style="color:#3a3a55" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-sm" style="color:#3a3a55">Aucune vente sur cette période.</p>
        </div>
        @else

        {{-- Cartes modes --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach ($byMode as $row)
            @php
                $mc    = $modeColors[$row->payment_mode] ?? ['hex' => '#88889a', 'bg' => 'rgba(136,136,154,.1)'];
                $share = $totals['total'] > 0 ? round($row->total / $totals['total'] * 100, 1) : 0;
            @endphp
            <div class="rounded-xl p-4 transition-all"
                 style="background:#0d0d18;border:1px solid {{ $mc['hex'] }}22">
                {{-- Badge + share --}}
                <div class="flex items-center justify-between mb-3">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold"
                          style="background:{{ $mc['bg'] }};color:{{ $mc['hex'] }}">
                        <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $mc['hex'] }}"></span>
                        {{ $row->label }}
                    </span>
                    <span class="text-xs font-bold tabular-nums" style="color:{{ $mc['hex'] }}">{{ $share }}%</span>
                </div>
                {{-- Montant --}}
                <div class="text-xl font-black tabular-nums mb-0.5" style="color:#e0e0ee">
                    {{ number_format($row->total, 0, ',', ' ') }}
                </div>
                <div class="text-xs mb-3" style="color:#3a3a55">FCFA</div>
                {{-- Meta --}}
                <div class="flex justify-between text-xs mb-2.5" style="color:#545470">
                    <span>{{ number_format($row->cnt) }} trans.</span>
                    <span>moy. {{ number_format($row->avg_ticket, 0, ',', ' ') }}</span>
                </div>
                {{-- Progress --}}
                <div class="h-1 rounded-full" style="background:rgba(255,255,255,.06)">
                    <div class="h-1 rounded-full transition-all" style="width:{{ min($share, 100) }}%;background:{{ $mc['hex'] }}"></div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Table récap --}}
        <div class="rounded-xl overflow-hidden" style="border:1px solid rgba(255,255,255,.06)">
            <table class="min-w-full text-sm">
                <thead>
                    <tr style="background:rgba(22,22,37,.9);border-bottom:1px solid rgba(255,255,255,.06)">
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color:#545470">Mode de paiement</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">Transactions</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">Montant</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">Ticket moyen</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color:#545470">Part</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($byMode as $row)
                    @php
                        $mc    = $modeColors[$row->payment_mode] ?? ['hex' => '#88889a', 'bg' => 'rgba(136,136,154,.1)'];
                        $share = $totals['total'] > 0 ? round($row->total / $totals['total'] * 100, 1) : 0;
                    @endphp
                    <tr style="border-bottom:1px solid rgba(255,255,255,.04)"
                        onmouseover="this.style.background='rgba(255,255,255,.02)'"
                        onmouseout="this.style.background=''">
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                  style="background:{{ $mc['bg'] }};color:{{ $mc['hex'] }}">
                                <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $mc['hex'] }}"></span>
                                {{ $row->label }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right tabular-nums text-sm" style="color:#88889a">{{ number_format($row->cnt) }}</td>
                        <td class="px-4 py-3.5 text-right tabular-nums text-sm font-bold" style="color:#e0e0ee">{{ number_format($row->total, 0, ',', ' ') }}</td>
                        <td class="px-4 py-3.5 text-right tabular-nums text-xs" style="color:#545470">{{ number_format($row->avg_ticket, 0, ',', ' ') }}</td>
                        <td class="px-4 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <div class="w-16 h-1.5 rounded-full overflow-hidden" style="background:rgba(255,255,255,.06)">
                                    <div class="h-1.5 rounded-full" style="width:{{ min($share, 100) }}%;background:{{ $mc['hex'] }}"></div>
                                </div>
                                <span class="text-xs tabular-nums font-semibold" style="color:{{ $mc['hex'] }}">{{ $share }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot style="background:rgba(22,22,37,.6);border-top:1px solid rgba(255,255,255,.06)">
                    <tr>
                        <td class="px-4 py-3 text-xs font-bold uppercase tracking-wider" style="color:#545470">Total</td>
                        <td class="px-4 py-3 text-right tabular-nums text-sm font-bold" style="color:#e0e0ee">{{ number_format($totals['cnt']) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums text-sm font-black" style="color:#d4af37">{{ number_format($totals['total'], 0, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-right text-xs" style="color:#3a3a55">—</td>
                        <td class="px-4 py-3 text-right text-sm font-bold" style="color:#e0e0ee">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @endif
    </div>

    {{-- ══════════════ VUE JOURNALIÈRE ══════════════ --}}
    @else
    <div class="p-5 space-y-4">
        @forelse ($daily->groupBy('date') as $date => $rows)
        @php
            $dayTotal = $rows->sum('total');
            $dayCnt   = $rows->sum('cnt');
        @endphp
        <div class="rounded-xl overflow-hidden" style="border:1px solid rgba(255,255,255,.06)">
            {{-- En-tête jour --}}
            <div class="flex items-center justify-between px-4 py-3" style="background:rgba(22,22,37,.9)">
                <div class="flex items-center gap-3">
                    <div class="text-sm font-bold capitalize" style="color:#e0e0ee">
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('l') }}
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded" style="background:rgba(255,255,255,.06);color:#545470;font-family:monospace">
                        {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                    </span>
                </div>
                <div class="flex items-center gap-4 text-right">
                    <div>
                        <div class="text-xs" style="color:#3a3a55">{{ number_format($dayCnt) }} trans.</div>
                    </div>
                    <div class="text-sm font-bold tabular-nums" style="color:#d4af37">
                        {{ number_format($dayTotal, 0, ',', ' ') }} <span class="text-xs font-normal" style="color:#3a3a55">FCFA</span>
                    </div>
                </div>
            </div>
            {{-- Modes du jour --}}
            <div class="p-3 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                @foreach ($rows as $row)
                @php $mc = $modeColors[$row->payment_mode] ?? ['hex' => '#88889a', 'bg' => 'rgba(136,136,154,.1)']; @endphp
                <div class="flex items-center justify-between px-3 py-2.5 rounded-lg"
                     style="background:{{ $mc['bg'] }};border:1px solid {{ $mc['hex'] }}18">
                    <span class="text-xs font-semibold" style="color:{{ $mc['hex'] }}">{{ $row->label }}</span>
                    <div class="text-right">
                        <div class="text-sm font-bold tabular-nums" style="color:#e0e0ee">{{ number_format($row->total, 0, ',', ' ') }}</div>
                        <div class="text-xs" style="color:#3a3a55">{{ number_format($row->cnt) }} trans.</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="flex flex-col items-center gap-3 py-20">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center" style="background:rgba(255,255,255,.04)">
                <svg class="h-7 w-7" style="color:#3a3a55" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-sm" style="color:#3a3a55">Aucune vente sur cette période.</p>
        </div>
        @endforelse
    </div>
    @endif

</div>
