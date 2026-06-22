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
            <label class="block text-xs text-night-300 mb-1">Grouper par</label>
            <select wire:model.live="groupBy"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                <option value="day">Jour</option>
                <option value="product">Produit</option>
                <option value="category">Catégorie</option>
                <option value="payment_mode">Mode paiement</option>
                <option value="transactions">Détail transactions</option>
            </select>
        </div>
        <div class="ml-auto flex gap-2">
            @can('export-reports')
                <a href="{{ route('reports.sales.pdf', array_filter([
                        'dateFrom' => $dateFrom,
                        'dateTo'   => $dateTo,
                        'groupBy'  => $groupBy,
                    ])) }}" target="_blank"
                    class="px-4 py-2 bg-red-700 text-white text-sm font-semibold rounded-md hover:bg-red-600">
                    ↓ PDF
                </a>
                <button wire:click="export" wire:loading.attr="disabled"
                    class="px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-md hover:bg-green-700 disabled:opacity-50">
                    <span wire:loading.remove wire:target="export">↓ Excel</span>
                    <span wire:loading wire:target="export">...</span>
                </button>
            @endcan
            <a href="{{ route('margins.index') }}"
                class="px-4 py-2 bg-neon-600/10 text-neon-400 text-sm font-medium rounded-md border border-neon-500/30 hover:bg-neon-600/20">
                Analyse marges →
            </a>
        </div>
    </div>

    {{-- Période affichée --}}
    <p class="text-xs text-night-300 mb-5">
        Du <strong>{{ $dateFrom }}</strong> au <strong>{{ $dateTo }}</strong>
    </p>

    {{-- Cartes résumé --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        <div class="rounded-xl p-4 text-center" style="background:rgba(139,92,246,.08);border:1px solid rgba(139,92,246,.2);border-top:2px solid #8b5cf6">
            <div class="panel-header mb-1.5">Transactions</div>
            <div class="text-2xl font-black text-neon-200 tabular-nums">{{ number_format($summary['count']) }}</div>
        </div>
        <div class="rounded-xl p-4 text-center" style="background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.2);border-top:2px solid #34d399">
            <div class="panel-header mb-1.5" style="color:#34d399">CA total</div>
            <div class="text-2xl font-black text-emerald-300 tabular-nums">{{ number_format($summary['total'], 0, ',', ' ') }}</div>
            <div class="text-[10px] text-emerald-500 mt-0.5">FCFA</div>
        </div>
        <div class="rounded-xl p-4 text-center" style="background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.2);border-top:2px solid #d4af37">
            <div class="panel-header mb-1.5" style="color:#d4af37">Remises</div>
            <div class="text-2xl font-black text-amber-300 tabular-nums">{{ number_format($summary['discounts'], 0, ',', ' ') }}</div>
            <div class="text-[10px] text-amber-500 mt-0.5">FCFA</div>
        </div>
        <div class="rounded-xl p-4 text-center" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-top:2px solid #60a5fa">
            <div class="panel-header mb-1.5" style="color:#60a5fa">Ticket moyen</div>
            <div class="text-2xl font-black text-blue-300 tabular-nums">{{ number_format($summary['avg_ticket'], 0, ',', ' ') }}</div>
            <div class="text-[10px] text-blue-500 mt-0.5">FCFA</div>
        </div>
        <div class="rounded-xl p-4 text-center" style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-top:2px solid #ef4444">
            <div class="panel-header mb-1.5" style="color:#ef4444">Annulations</div>
            <div class="text-2xl font-black text-red-300 tabular-nums">{{ number_format($summary['cancelled_count']) }}</div>
        </div>
    </div>

    {{-- Table données --}}
    <div class="overflow-x-auto">
        @if($groupBy === 'transactions')
        {{-- Vue détail transactions --}}
        <table class="min-w-full divide-y divide-white/5 text-sm">
            <thead class="bg-night-700/60 border-b border-white/5">
                <tr class="tbl-head">
                    <th class="px-3 py-2.5 text-left">N° Vente</th>
                    <th class="px-3 py-2.5 text-left">Date / Heure</th>
                    <th class="px-3 py-2.5 text-left">Produits vendus</th>
                    <th class="px-3 py-2.5 text-right">Qté</th>
                    <th class="px-3 py-2.5 text-left">Mode paiement</th>
                    <th class="px-3 py-2.5 text-right">CA (FCFA)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($rows as $row)
                    <tr wire:key="t-{{ $row->id }}" class="hover:bg-white/[0.04] transition-colors {{ $loop->even ? 'bg-white/[0.02]' : '' }}">
                        <td class="px-3 py-3 font-mono text-xs text-neon-300">{{ $row->label }}</td>
                        <td class="px-3 py-3 text-night-200 text-xs whitespace-nowrap">{{ $row->datetime }}</td>
                        <td class="px-3 py-3 text-night-100 text-xs leading-relaxed">
                            @foreach(explode(' · ', $row->products_detail ?? '') as $item)
                                <span class="inline-block bg-night-600 rounded px-1.5 py-0.5 mr-1 mb-0.5 text-[11px]">{{ $item }}</span>
                            @endforeach
                        </td>
                        <td class="px-3 py-3 text-right text-night-200">{{ number_format($row->qty ?? 0) }}</td>
                        <td class="px-3 py-3 text-xs text-night-300">
                            {{ match($row->payment_mode) {
                                'cash'         => '💵 Espèces',
                                'card'         => '💳 Carte',
                                'mobile_money' => '📱 Mobile Money',
                                'orange_money' => '🟠 Orange Money',
                                'moov_money'   => '🔵 Moov Money',
                                'wave'         => '〰️ Wave',
                                'credit'       => '📋 Crédit',
                                default        => $row->payment_mode,
                            } }}
                        </td>
                        <td class="px-3 py-3 text-right font-semibold text-night-50">
                            {{ number_format($row->total, 0, ',', ' ') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-night-300">Aucune vente sur cette période.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($rows->isNotEmpty())
                <tfoot class="bg-night-700 border-t-2 border-white/10 font-semibold text-night-100">
                    <tr>
                        <td class="px-3 py-3" colspan="3">TOTAL — {{ number_format($summary['count']) }} transaction(s)</td>
                        <td class="px-3 py-3 text-right">{{ number_format($rows->sum('qty')) }}</td>
                        <td class="px-3 py-3"></td>
                        <td class="px-3 py-3 text-right">{{ number_format($summary['total'], 0, ',', ' ') }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
        @else
        {{-- Vue agrégée (jour / produit / catégorie / mode paiement) --}}
        <table class="min-w-full divide-y divide-white/5 text-sm">
            <thead class="bg-night-700/60 border-b border-white/5">
                <tr class="tbl-head">
                    <th class="px-3 py-2.5 text-left">
                        {{ match($groupBy) {
                            'day'          => 'Date',
                            'product'      => 'Produit',
                            'category'     => 'Catégorie',
                            'payment_mode' => 'Mode paiement',
                            default        => 'Libellé',
                        } }}
                    </th>
                    @if(in_array($groupBy, ['day','payment_mode']))
                        <th class="px-3 py-2.5 text-right">Transactions</th>
                    @else
                        <th class="px-3 py-2.5 text-right">Qté</th>
                    @endif
                    <th class="px-3 py-2.5 text-right">CA (FCFA)</th>
                    @if($groupBy === 'day')
                        <th class="px-3 py-2.5 text-right">Remises</th>
                        <th class="px-3 py-2.5 text-right">Ticket moy.</th>
                    @endif
                    <th class="px-3 py-2.5 text-right">% CA</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($rows as $row)
                    @php $share = round($row->total / $grandTotal * 100, 1); @endphp
                    <tr wire:key="r-{{ $loop->index }}" class="hover:bg-white/[0.04] transition-colors {{ $loop->even ? 'bg-white/[0.02]' : '' }}">
                        <td class="px-3 py-3 font-medium text-night-50">{{ $row->label }}</td>
                        @if(in_array($groupBy, ['day','payment_mode']))
                            <td class="px-3 py-3 text-right text-night-200">{{ number_format($row->count) }}</td>
                        @else
                            <td class="px-3 py-3 text-right text-night-200">{{ number_format($row->qty ?? 0) }}</td>
                        @endif
                        <td class="px-3 py-3 text-right font-semibold text-night-50">
                            {{ number_format($row->total, 0, ',', ' ') }}
                        </td>
                        @if($groupBy === 'day')
                            <td class="px-3 py-3 text-right text-amber-400 text-xs">
                                {{ number_format($row->discounts, 0, ',', ' ') }}
                            </td>
                            <td class="px-3 py-3 text-right text-night-300 text-xs">
                                {{ number_format($row->avg_ticket, 0, ',', ' ') }}
                            </td>
                        @endif
                        <td class="px-3 py-3 text-right text-xs text-night-300">
                            <div class="flex items-center justify-end gap-1">
                                <div class="w-12 bg-night-700 rounded-full h-1.5">
                                    <div class="bg-blue-400 h-1.5 rounded-full" style="width: {{ min($share, 100) }}%"></div>
                                </div>
                                {{ $share }}%
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-night-300">Aucune vente sur cette période.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($rows->isNotEmpty())
                <tfoot class="bg-night-700 border-t-2 border-white/10 font-semibold text-night-100">
                    <tr>
                        <td class="px-3 py-3">TOTAL</td>
                        <td class="px-3 py-3 text-right">{{ number_format($summary['count']) }}</td>
                        <td class="px-3 py-3 text-right">{{ number_format($summary['total'], 0, ',', ' ') }}</td>
                        @if($groupBy === 'day')
                            <td class="px-3 py-3 text-right text-amber-400">{{ number_format($summary['discounts'], 0, ',', ' ') }}</td>
                            <td class="px-3 py-3 text-right">{{ number_format($summary['avg_ticket'], 0, ',', ' ') }}</td>
                        @endif
                        <td class="px-3 py-3 text-right">100%</td>
                    </tr>
                </tfoot>
            @endif
        </table>
        @endif
    </div>

</div>
