<div class="p-6">

    {{-- Filtres --}}
    <div class="flex flex-wrap items-end gap-3 mb-6">
        <div>
            <label class="block text-xs text-night-300 mb-1">Période</label>
            <select wire:model.live="period"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                <option value="today">Aujourd'hui</option>
                <option value="week">Cette semaine</option>
                <option value="month">Ce mois</option>
                <option value="year">Cette année</option>
                <option value="custom">Personnalisée</option>
            </select>
        </div>
        @if($period === 'custom')
            <div>
                <label class="block text-xs text-night-300 mb-1">Du</label>
                <input wire:model.live="dateFrom" type="date"
                    class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
            </div>
            <div>
                <label class="block text-xs text-night-300 mb-1">Au</label>
                <input wire:model.live="dateTo" type="date"
                    class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
            </div>
        @endif
        <div>
            <label class="block text-xs text-night-300 mb-1">Vue</label>
            <select wire:model.live="view"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                <option value="modes">Par mode</option>
                <option value="daily">Détail journalier</option>
            </select>
        </div>
        <div class="ml-auto flex gap-2">
            @can('export-reports')
                <a href="{{ route('reports.payments.pdf', array_filter([
                        'dateFrom' => $dateFrom,
                        'dateTo'   => $dateTo,
                    ])) }}" target="_blank"
                    class="px-4 py-2 bg-red-700 text-white text-sm font-semibold rounded-md hover:bg-red-600">
                    ↓ PDF
                </a>
            @endcan
        </div>
    </div>

    <p class="text-xs text-night-300 mb-5">
        Du <strong>{{ $dateFrom }}</strong> au <strong>{{ $dateTo }}</strong> —
        <strong>{{ number_format($totals['cnt']) }}</strong> transactions,
        <strong>{{ number_format($totals['total'], 0, ',', ' ') }}</strong> FCFA encaissés
    </p>

    {{-- Vue : par mode --}}
    @if($view === 'modes')
        {{-- Cartes résumé par mode --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            @forelse($byMode as $row)
                @php $share = $totals['total'] > 0 ? round($row->total / $totals['total'] * 100, 1) : 0; @endphp
                <div class="bg-night-700 border border-white/8 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $row->badge_class }}">
                            {{ $row->label }}
                        </span>
                        <span class="text-xs text-night-400">{{ $share }}%</span>
                    </div>
                    <div class="text-xl font-bold text-night-50">{{ number_format($row->total, 0, ',', ' ') }}</div>
                    <div class="text-xs text-night-400 mt-0.5">FCFA</div>
                    <div class="mt-2 flex items-center justify-between text-xs text-night-300">
                        <span>{{ number_format($row->cnt) }} trans.</span>
                        <span>moy. {{ number_format($row->avg_ticket, 0, ',', ' ') }}</span>
                    </div>
                    <div class="mt-2 w-full bg-night-600 rounded-full h-1">
                        <div class="h-1 rounded-full bg-neon-500" style="width: {{ min($share, 100) }}%"></div>
                    </div>
                </div>
            @empty
                <div class="col-span-4 text-center text-night-300 py-8">Aucune vente sur cette période.</div>
            @endforelse
        </div>

        {{-- Table récap --}}
        @if($byMode->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-white/5 text-sm">
                    <thead class="bg-night-700">
                        <tr>
                            <th class="px-3 py-3 text-left font-medium text-night-200">Mode de paiement</th>
                            <th class="px-3 py-3 text-right font-medium text-night-200">Transactions</th>
                            <th class="px-3 py-3 text-right font-medium text-night-200">Montant (FCFA)</th>
                            <th class="px-3 py-3 text-right font-medium text-night-200">Ticket moyen</th>
                            <th class="px-3 py-3 text-right font-medium text-night-200">% du CA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($byMode as $row)
                            @php $share = $totals['total'] > 0 ? round($row->total / $totals['total'] * 100, 1) : 0; @endphp
                            <tr>
                                <td class="px-3 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $row->badge_class }}">
                                        {{ $row->label }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-right text-night-200">{{ number_format($row->cnt) }}</td>
                                <td class="px-3 py-3 text-right font-semibold text-night-50">{{ number_format($row->total, 0, ',', ' ') }}</td>
                                <td class="px-3 py-3 text-right text-night-300 text-xs">{{ number_format($row->avg_ticket, 0, ',', ' ') }}</td>
                                <td class="px-3 py-3 text-right text-xs text-night-300">
                                    <div class="flex items-center justify-end gap-1">
                                        <div class="w-12 bg-night-700 rounded-full h-1.5">
                                            <div class="bg-neon-500 h-1.5 rounded-full" style="width: {{ min($share, 100) }}%"></div>
                                        </div>
                                        {{ $share }}%
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-night-700 border-t-2 border-white/10 font-semibold text-night-100">
                        <tr>
                            <td class="px-3 py-3">TOTAL</td>
                            <td class="px-3 py-3 text-right">{{ number_format($totals['cnt']) }}</td>
                            <td class="px-3 py-3 text-right">{{ number_format($totals['total'], 0, ',', ' ') }}</td>
                            <td class="px-3 py-3 text-right">—</td>
                            <td class="px-3 py-3 text-right">100%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

    {{-- Vue : détail journalier --}}
    @else
        @php
            $days = $daily->groupBy('date');
        @endphp
        @forelse($days as $date => $rows)
            <div class="mb-4">
                <div class="text-xs font-semibold text-night-300 mb-2 border-b border-white/5 pb-1">
                    {{ \Carbon\Carbon::parse($date)->translatedFormat('l d/m/Y') }}
                    <span class="ml-2 text-night-400">— {{ number_format($rows->sum('total'), 0, ',', ' ') }} FCFA / {{ number_format($rows->sum('cnt')) }} trans.</span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach($rows as $row)
                        <div class="flex items-center justify-between bg-night-700 rounded-lg px-3 py-2">
                            <span class="px-1.5 py-0.5 rounded-full text-xs font-medium {{ $row->badge_class }}">{{ $row->label }}</span>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-night-50">{{ number_format($row->total, 0, ',', ' ') }}</div>
                                <div class="text-xs text-night-400">{{ number_format($row->cnt) }} trans.</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center text-night-300 py-8">Aucune vente sur cette période.</div>
        @endforelse
    @endif

</div>
