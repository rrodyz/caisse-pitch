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
            <label class="block text-xs text-night-300 mb-1">Type</label>
            <select wire:model.live="lossType"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                <option value="">Tous les types</option>
                <option value="loss">Perte</option>
                <option value="break">Casse</option>
                <option value="gift">Offert / Gratuit</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-night-300 mb-1">Produit</label>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher..."
                class="border-white/10 rounded-md shadow-sm text-sm w-40 focus:ring-neon-500/30 focus:border-neon-500">
        </div>
        <div class="ml-auto flex gap-2">
            @can('export-reports')
                <a href="{{ route('reports.losses.pdf', array_filter([
                        'dateFrom' => $dateFrom,
                        'dateTo'   => $dateTo,
                        'lossType' => $lossType,
                    ])) }}" target="_blank"
                    class="px-4 py-2 bg-red-700 text-white text-sm font-semibold rounded-md hover:bg-red-600">
                    ↓ PDF
                </a>
            @endcan
        </div>
    </div>

    <p class="text-xs text-night-300 mb-5">
        Du <strong>{{ $dateFrom }}</strong> au <strong>{{ $dateTo }}</strong>
    </p>

    {{-- Cartes résumé --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-night-700 border border-white/8 rounded-xl p-4 text-center">
            <div class="text-xs text-night-300 mb-1">Déclarations</div>
            <div class="text-xl font-bold text-night-50">{{ number_format($summary['count']) }}</div>
        </div>
        <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-center">
            <div class="text-xs text-red-400 mb-1">Coût total</div>
            <div class="text-xl font-bold text-red-300">{{ number_format($summary['cost'], 0, ',', ' ') }}</div>
            <div class="text-xs text-red-400">FCFA</div>
        </div>
        <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-center">
            <div class="text-xs text-red-400 mb-1">Pertes</div>
            <div class="text-xl font-bold text-red-300">{{ number_format($summary['by_type']['loss']['cost'], 0, ',', ' ') }}</div>
            <div class="text-xs text-red-400">{{ number_format($summary['by_type']['loss']['cnt']) }} déc.</div>
        </div>
        <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 text-center">
            <div class="text-xs text-amber-400 mb-1">Casses</div>
            <div class="text-xl font-bold text-amber-300">{{ number_format($summary['by_type']['break']['cost'], 0, ',', ' ') }}</div>
            <div class="text-xs text-amber-400">{{ number_format($summary['by_type']['break']['cnt']) }} déc.</div>
        </div>
        <div class="bg-purple-500/10 border border-purple-500/20 rounded-xl p-4 text-center">
            <div class="text-xs text-purple-400 mb-1">Offerts</div>
            <div class="text-xl font-bold text-purple-300">{{ number_format($summary['by_type']['gift']['cost'], 0, ',', ' ') }}</div>
            <div class="text-xs text-purple-400">{{ number_format($summary['by_type']['gift']['cnt']) }} déc.</div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/5 text-sm">
            <thead class="bg-night-700">
                <tr>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Date</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Type</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Produit</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">Qté</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">P.U. (FCFA)</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">Coût total</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Motif</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Déclaré par</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($rows as $row)
                    <tr wire:key="loss-{{ $row->id }}">
                        <td class="px-3 py-3 text-night-300 text-xs whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($row->created_at)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-3 py-3">
                            @php
                                $badge = match($row->type) {
                                    'loss'  => 'bg-red-500/15 text-red-300',
                                    'break' => 'bg-amber-500/15 text-amber-300',
                                    'gift'  => 'bg-purple-500/15 text-purple-300',
                                    default => 'bg-night-700 text-night-300',
                                };
                                $label = match($row->type) {
                                    'loss'  => 'Perte',
                                    'break' => 'Casse',
                                    'gift'  => 'Offert',
                                    default => $row->type,
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ $label }}</span>
                        </td>
                        <td class="px-3 py-3 font-medium text-night-50">{{ $row->product_name }}</td>
                        <td class="px-3 py-3 text-right text-night-200">
                            {{ number_format($row->quantity, 2) }} {{ $row->product_unit }}
                        </td>
                        <td class="px-3 py-3 text-right text-night-300 text-xs">
                            {{ $row->unit_cost ? number_format($row->unit_cost, 0, ',', ' ') : '—' }}
                        </td>
                        <td class="px-3 py-3 text-right font-semibold text-night-50">
                            {{ number_format($row->total_cost, 0, ',', ' ') }}
                        </td>
                        <td class="px-3 py-3 text-night-300 text-xs max-w-xs truncate">{{ $row->reason ?? '—' }}</td>
                        <td class="px-3 py-3 text-night-300 text-xs">{{ $row->user_name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-night-300">
                            Aucune déclaration sur cette période.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $rows->links() }}</div>

</div>
