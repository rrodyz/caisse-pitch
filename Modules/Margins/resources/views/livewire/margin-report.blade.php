<div class="p-6">

    {{-- Filtres --}}
    <div class="flex flex-wrap items-end gap-3 mb-6">
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
        <div>
            <label class="block text-xs text-night-300 mb-1">Catégorie</label>
            <select wire:model.live="categoryId"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                <option value="">Toutes</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-night-300 mb-1">Grouper par</label>
            <select wire:model.live="groupBy"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                <option value="product">Produit</option>
                <option value="category">Catégorie</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-night-300 mb-1">Trier par</label>
            <select wire:model.live="sortBy"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                <option value="revenue">CA</option>
                <option value="margin">Taux marge</option>
                <option value="qty">Quantité</option>
            </select>
        </div>
        <div class="ml-auto">
            <button wire:click="export" wire:loading.attr="disabled"
                class="px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-md hover:bg-green-700 disabled:opacity-50">
                <span wire:loading.remove wire:target="export">↓ Export Excel</span>
                <span wire:loading wire:target="export">Export...</span>
            </button>
        </div>
    </div>

    {{-- Cartes résumé --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-neon-600/10 border border-neon-500/20 rounded-xl p-4 text-center">
            <div class="text-xs text-neon-300 mb-1">CA total</div>
            <div class="text-lg font-bold text-neon-200">{{ number_format($summary['total_revenue'], 0, ',', ' ') }}</div>
            <div class="text-xs text-neon-400">FCFA</div>
        </div>
        <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-center">
            <div class="text-xs text-red-400 mb-1">Coût total</div>
            <div class="text-lg font-bold text-red-300">{{ number_format($summary['total_cost'], 0, ',', ' ') }}</div>
            <div class="text-xs text-red-400">FCFA</div>
        </div>
        <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-4 text-center">
            <div class="text-xs text-emerald-400 mb-1">Marge brute</div>
            <div class="text-lg font-bold text-emerald-300">{{ number_format($summary['total_margin'], 0, ',', ' ') }}</div>
            <div class="text-xs text-emerald-400">FCFA</div>
        </div>
        <div class="bg-neon-600/10 border border-neon-500/20 rounded-xl p-4 text-center">
            <div class="text-xs text-neon-300 mb-1">Taux marge moy.</div>
            <div class="text-lg font-bold text-neon-400">{{ number_format($summary['avg_margin'] ?? 0, 1) }}%</div>
        </div>
        <div class="bg-night-700 rounded-xl p-4 text-center">
            <div class="text-xs text-night-300 mb-1">Qté vendue</div>
            <div class="text-lg font-bold text-night-100">{{ number_format($summary['total_qty'], 0, ',', ' ') }}</div>
        </div>
    </div>

    {{-- Table marges --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/5 text-sm">
            <thead class="bg-night-700">
                <tr>
                    <th class="px-3 py-3 text-left font-medium text-night-200">
                        {{ $groupBy === 'category' ? 'Catégorie' : 'Produit' }}
                    </th>
                    @if($groupBy === 'product')
                        <th class="px-3 py-3 text-left font-medium text-night-200">Catégorie</th>
                    @else
                        <th class="px-3 py-3 text-center font-medium text-night-200">Produits</th>
                    @endif
                    <th class="px-3 py-3 text-right font-medium text-night-200">Qté</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">CA (FCFA)</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">Coût (FCFA)</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">Marge (FCFA)</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">Taux marge</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">Markup</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">% CA</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @php $totalRevenue = $summary['total_revenue'] ?: 1; @endphp
                @forelse($rows as $row)
                    @php
                        $marginColor = $row->margin_rate >= 60 ? 'text-emerald-400' :
                                      ($row->margin_rate >= 30 ? 'text-yellow-600' : 'text-red-500');
                        $caShare = round($row->total_revenue / $totalRevenue * 100, 1);
                    @endphp
                    <tr wire:key="row-{{ $loop->index }}">
                        <td class="px-3 py-3 font-medium text-night-50">{{ $row->label }}</td>
                        @if($groupBy === 'product')
                            <td class="px-3 py-3 text-xs text-night-200">{{ $row->category_name }}</td>
                        @else
                            <td class="px-3 py-3 text-center text-night-300 text-xs">{{ $row->product_count }}</td>
                        @endif
                        <td class="px-3 py-3 text-right text-night-200">{{ number_format($row->total_qty, 0, ',', ' ') }}</td>
                        <td class="px-3 py-3 text-right font-semibold text-night-50">{{ number_format($row->total_revenue, 0, ',', ' ') }}</td>
                        <td class="px-3 py-3 text-right text-red-400">{{ number_format($row->total_cost, 0, ',', ' ') }}</td>
                        <td class="px-3 py-3 text-right font-semibold {{ $row->gross_margin >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ number_format($row->gross_margin, 0, ',', ' ') }}
                        </td>
                        <td class="px-3 py-3 text-right font-bold {{ $marginColor }}">
                            {{ $row->margin_rate }}%
                        </td>
                        <td class="px-3 py-3 text-right text-night-300 text-xs">
                            {{ $row->markup_rate !== null ? $row->markup_rate . '%' : '—' }}
                        </td>
                        <td class="px-3 py-3 text-right text-xs text-night-300">
                            <div class="flex items-center justify-end gap-1">
                                <div class="w-16 bg-night-700 rounded-full h-1.5">
                                    <div class="bg-indigo-400 h-1.5 rounded-full" style="width: {{ min($caShare, 100) }}%"></div>
                                </div>
                                {{ $caShare }}%
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-night-300">
                            Aucune vente sur cette période.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($rows->isNotEmpty())
                <tfoot class="bg-night-700 border-t-2 border-white/10">
                    <tr class="font-semibold text-night-100">
                        <td class="px-3 py-3" colspan="{{ $groupBy === 'product' ? 2 : 2 }}">TOTAL</td>
                        <td class="px-3 py-3 text-right">{{ number_format($summary['total_qty'], 0, ',', ' ') }}</td>
                        <td class="px-3 py-3 text-right">{{ number_format($summary['total_revenue'], 0, ',', ' ') }}</td>
                        <td class="px-3 py-3 text-right text-red-400">{{ number_format($summary['total_cost'], 0, ',', ' ') }}</td>
                        <td class="px-3 py-3 text-right text-emerald-400">{{ number_format($summary['total_margin'], 0, ',', ' ') }}</td>
                        <td class="px-3 py-3 text-right">{{ number_format($summary['avg_margin'] ?? 0, 1) }}%</td>
                        <td class="px-3 py-3" colspan="2"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    {{-- Légende couleurs --}}
    <div class="mt-4 flex items-center gap-4 text-xs text-night-200">
        <span>Légende taux marge :</span>
        <span class="text-emerald-400 font-medium">≥ 60% — Excellent</span>
        <span class="text-yellow-600 font-medium">30–60% — Correct</span>
        <span class="text-red-500 font-medium">&lt; 30% — Faible</span>
    </div>

</div>
