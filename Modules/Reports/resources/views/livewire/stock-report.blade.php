<div class="p-6">

    {{-- Onglets --}}
    <div class="flex items-center justify-between mb-5">
        <div class="border-b border-white/8 w-full">
            <nav class="-mb-px flex space-x-6">
                @foreach(['valuation' => 'Valorisation stock', 'movements' => 'Mouvements', 'alerts' => 'Alertes stock'] as $tab => $label)
                    <button wire:click="$set('view', '{{ $tab }}')"
                        class="pb-3 text-sm font-medium border-b-2 {{ $view === $tab ? 'border-indigo-500 text-neon-400' : 'border-transparent text-night-300 hover:text-night-200 hover:border-white/10' }}">
                        {{ $label }}
                        @if($tab === 'alerts' && isset($data['rows']))
                            <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-500/15 text-red-400">
                                {{ $data['rows']->count() }}
                            </span>
                        @endif
                    </button>
                @endforeach
                <div class="ml-auto pb-3 flex items-center gap-2">
                    @can('export-reports')
                        <a href="{{ route('reports.stock.pdf', array_filter([
                                'view'       => $view,
                                'search'     => $search,
                                'categoryId' => $categoryId,
                                'sortBy'     => $sortBy,
                                'dateFrom'   => $dateFrom,
                                'dateTo'     => $dateTo,
                                'filterType' => $filterType,
                            ])) }}" target="_blank"
                            class="px-3 py-1.5 bg-red-700 text-white text-xs font-semibold rounded hover:bg-red-600">
                            ↓ PDF
                        </a>
                        <button wire:click="export" wire:loading.attr="disabled"
                            class="px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700 disabled:opacity-50">
                            <span wire:loading.remove wire:target="export">↓ Excel</span>
                            <span wire:loading wire:target="export">...</span>
                        </button>
                    @endcan
                </div>
            </nav>
        </div>
    </div>

    {{-- VALORISATION --}}
    @if($view === 'valuation' && isset($data['rows']))
        {{-- Filtres --}}
        <div class="flex flex-wrap items-end gap-3 mb-4">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher produit..."
                class="border-white/10 rounded-md shadow-sm text-sm w-48 focus:ring-neon-500/30 focus:border-neon-500">
            <select wire:model.live="categoryId"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                <option value="">Toutes catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="sortBy"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                <option value="value">Trier : Valeur</option>
                <option value="qty">Trier : Quantité</option>
                <option value="name">Trier : Nom</option>
            </select>
        </div>

        {{-- Cartes résumé --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
            <div class="bg-neon-600/10 border border-neon-500/20 rounded-xl p-4 text-center">
                <div class="text-xs text-neon-300 mb-1">Valeur totale stock</div>
                <div class="text-lg font-bold text-neon-200">{{ number_format($data['summary']['total_value'], 0, ',', ' ') }}</div>
                <div class="text-xs text-neon-400">FCFA</div>
            </div>
            <div class="bg-night-700 rounded-xl p-4 text-center">
                <div class="text-xs text-night-300 mb-1">Références actives</div>
                <div class="text-lg font-bold text-night-100">{{ $data['summary']['total_products'] }}</div>
            </div>
            <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 text-center">
                <div class="text-xs text-amber-400 mb-1">Stock bas</div>
                <div class="text-lg font-bold text-amber-300">{{ $data['summary']['low_stock_count'] }}</div>
            </div>
            <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-center">
                <div class="text-xs text-red-400 mb-1">Ruptures</div>
                <div class="text-lg font-bold text-red-300">{{ $data['summary']['out_stock_count'] }}</div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/5 text-sm">
                <thead class="bg-night-700">
                    <tr>
                        <th class="px-3 py-3 text-left font-medium text-night-200">Produit</th>
                        <th class="px-3 py-3 text-left font-medium text-night-200">Catégorie</th>
                        <th class="px-3 py-3 text-right font-medium text-night-200">Stock</th>
                        <th class="px-3 py-3 text-right font-medium text-night-200">Mini</th>
                        <th class="px-3 py-3 text-right font-medium text-night-200">Px achat</th>
                        <th class="px-3 py-3 text-right font-medium text-night-200">Valeur (FCFA)</th>
                        <th class="px-3 py-3 text-center font-medium text-night-200">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($data['rows'] as $row)
                        <tr wire:key="v-{{ $row->id }}">
                            <td class="px-3 py-3 font-medium text-night-50">{{ $row->name }}</td>
                            <td class="px-3 py-3 text-xs text-night-200">{{ $row->category_name ?? '—' }}</td>
                            <td class="px-3 py-3 text-right {{ $row->stock_quantity <= 0 ? 'text-red-400 font-bold' : ($row->stock_quantity <= $row->min_stock ? 'text-amber-400 font-semibold' : 'text-night-100') }}">
                                {{ number_format($row->stock_quantity, 2) }} {{ $row->unit }}
                            </td>
                            <td class="px-3 py-3 text-right text-night-300 text-xs">{{ number_format($row->min_stock, 2) }}</td>
                            <td class="px-3 py-3 text-right text-night-300 text-xs">{{ number_format($row->purchase_price, 0, ',', ' ') }}</td>
                            <td class="px-3 py-3 text-right font-semibold text-night-50">{{ number_format($row->stock_value, 0, ',', ' ') }}</td>
                            <td class="px-3 py-3 text-center">
                                <span class="inline-flex px-2 py-0.5 text-xs rounded-full font-medium
                                    {{ $row->stock_status === 'ok' ? 'bg-emerald-500/15 text-emerald-400' : ($row->stock_status === 'bas' ? 'bg-amber-500/15 text-amber-400' : 'bg-red-500/15 text-red-400') }}">
                                    {{ $row->stock_status === 'ok' ? 'OK' : ($row->stock_status === 'bas' ? 'Bas' : 'Rupture') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-night-300">Aucun produit.</td></tr>
                    @endforelse
                </tbody>
                @if($data['rows']->isNotEmpty())
                    <tfoot class="bg-night-700 border-t-2 border-white/10 font-semibold text-night-100">
                        <tr>
                            <td class="px-3 py-3" colspan="5">TOTAL</td>
                            <td class="px-3 py-3 text-right">{{ number_format($data['summary']['total_value'], 0, ',', ' ') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    @endif

    {{-- MOUVEMENTS --}}
    @if($view === 'movements' && isset($data['rows']))
        <div class="flex flex-wrap items-end gap-3 mb-4">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Produit..."
                class="border-white/10 rounded-md shadow-sm text-sm w-40 focus:ring-neon-500/30 focus:border-neon-500">
            <select wire:model.live="filterType"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                <option value="">Tous types</option>
                <option value="purchase_in">Achat entrant</option>
                <option value="sale_out">Vente sortante</option>
                <option value="loss">Perte</option>
                <option value="break">Casse</option>
                <option value="gift">Offert</option>
                <option value="inventory_adjustment">Ajustement inventaire</option>
                <option value="manual_in">Entrée manuelle</option>
                <option value="manual_out">Sortie manuelle</option>
            </select>
            <input wire:model.live="dateFrom" type="date"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
            <span class="text-night-300 text-sm">→</span>
            <input wire:model.live="dateTo" type="date"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/5 text-sm">
                <thead class="bg-night-700">
                    <tr>
                        <th class="px-3 py-3 text-left font-medium text-night-200">Date</th>
                        <th class="px-3 py-3 text-left font-medium text-night-200">Produit</th>
                        <th class="px-3 py-3 text-left font-medium text-night-200">Type</th>
                        <th class="px-3 py-3 text-right font-medium text-night-200">Avant</th>
                        <th class="px-3 py-3 text-right font-medium text-night-200">Mouvement</th>
                        <th class="px-3 py-3 text-right font-medium text-night-200">Après</th>
                        <th class="px-3 py-3 text-left font-medium text-night-200">Notes</th>
                        <th class="px-3 py-3 text-left font-medium text-night-200">Utilisateur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($data['rows'] as $row)
                        @php
                            $delta = $row->quantity_after - $row->quantity_before;
                            $deltaColor = $delta > 0 ? 'text-emerald-400' : 'text-red-400';
                        @endphp
                        <tr wire:key="m-{{ $row->id }}">
                            <td class="px-3 py-3 text-xs text-night-200">{{ \Carbon\Carbon::parse($row->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-3 font-medium text-night-100">{{ $row->product_name }}</td>
                            <td class="px-3 py-3 text-xs text-night-200">{{ str_replace('_', ' ', $row->type) }}</td>
                            <td class="px-3 py-3 text-right text-night-300 text-xs">{{ number_format($row->quantity_before, 3) }}</td>
                            <td class="px-3 py-3 text-right font-semibold {{ $deltaColor }}">
                                {{ $delta > 0 ? '+' : '' }}{{ number_format($delta, 3) }}
                            </td>
                            <td class="px-3 py-3 text-right text-night-200 text-xs">{{ number_format($row->quantity_after, 3) }}</td>
                            <td class="px-3 py-3 text-xs text-night-300 max-w-xs truncate">{{ $row->notes ?? '—' }}</td>
                            <td class="px-3 py-3 text-xs text-night-200">{{ $row->user_name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-night-300">Aucun mouvement.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $data['rows']->links() }}</div>
    @endif

    {{-- ALERTES --}}
    @if($view === 'alerts' && isset($data['rows']))
        @if($data['rows']->isEmpty())
            <div class="py-12 text-center">
                <div class="text-emerald-400 text-4xl mb-3">✓</div>
                <p class="text-night-300 font-medium">Aucune alerte stock. Tous les niveaux sont satisfaisants.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-white/5 text-sm">
                    <thead class="bg-red-500/10">
                        <tr>
                            <th class="px-3 py-3 text-left font-medium text-night-200">Produit</th>
                            <th class="px-3 py-3 text-left font-medium text-night-200">Catégorie</th>
                            <th class="px-3 py-3 text-right font-medium text-night-200">Stock actuel</th>
                            <th class="px-3 py-3 text-right font-medium text-night-200">Stock mini</th>
                            <th class="px-3 py-3 text-right font-medium text-red-400">Manquant</th>
                            <th class="px-3 py-3 text-center font-medium text-night-200">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($data['rows'] as $row)
                            <tr wire:key="a-{{ $row->id }}">
                                <td class="px-3 py-3 font-medium text-night-50">{{ $row->name }}</td>
                                <td class="px-3 py-3 text-xs text-night-200">{{ $row->category_name ?? '—' }}</td>
                                <td class="px-3 py-3 text-right {{ $row->stock_quantity <= 0 ? 'text-red-400 font-bold' : 'text-amber-400 font-semibold' }}">
                                    {{ number_format($row->stock_quantity, 2) }} {{ $row->unit }}
                                </td>
                                <td class="px-3 py-3 text-right text-night-300 text-xs">{{ number_format($row->min_stock, 2) }}</td>
                                <td class="px-3 py-3 text-right font-bold text-red-400">
                                    {{ number_format($row->shortage, 2) }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 text-xs rounded-full font-medium
                                        {{ $row->stock_quantity <= 0 ? 'bg-red-500/15 text-red-400' : 'bg-amber-500/15 text-amber-400' }}">
                                        {{ $row->stock_quantity <= 0 ? 'Rupture' : 'Stock bas' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif

</div>
