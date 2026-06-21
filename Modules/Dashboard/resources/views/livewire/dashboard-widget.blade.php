<div class="p-5 space-y-5" wire:poll.60s>

    {{-- ── KPI row ────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

        {{-- CA jour --}}
        <div class="relative overflow-hidden rounded-xl bg-night-800 border border-white/5 p-5">
            <div class="absolute inset-0 bg-gradient-to-br from-gold-400/8 to-transparent pointer-events-none"></div>
            <div class="text-xs font-semibold text-night-200 uppercase tracking-wider mb-2">CA aujourd'hui</div>
            <div class="text-2xl font-bold text-white">{{ number_format($todayStats->total, 0, ',', ' ') }}</div>
            <div class="text-xs text-night-300 mt-0.5">FCFA</div>
            @if($yesterdayTotal > 0)
                @php $diff = round(($todayStats->total - $yesterdayTotal) / $yesterdayTotal * 100, 1); @endphp
                <div class="text-xs mt-2 font-medium {{ $diff >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                    {{ $diff >= 0 ? '▲' : '▼' }} {{ abs($diff) }}% vs hier
                </div>
            @endif
            <div class="absolute top-4 right-4 w-8 h-8 rounded-lg bg-gold-400/10 flex items-center justify-center">
                <svg class="h-4 w-4 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        {{-- Transactions --}}
        <div class="relative overflow-hidden rounded-xl bg-night-800 border border-white/5 p-5">
            <div class="text-xs font-semibold text-night-200 uppercase tracking-wider mb-2">Transactions</div>
            <div class="text-2xl font-bold text-white">{{ number_format($todayStats->count) }}</div>
            @if($todayCancelled > 0)
                <div class="text-xs text-red-400 mt-1.5 font-medium">{{ $todayCancelled }} annulation(s)</div>
            @endif
            @if($todayStats->discounts > 0)
                <div class="text-xs text-amber-400 mt-1">{{ number_format($todayStats->discounts, 0, ',', ' ') }} FCFA remises</div>
            @endif
            <div class="absolute top-4 right-4 w-8 h-8 rounded-lg bg-neon-500/10 flex items-center justify-center">
                <svg class="h-4 w-4 text-neon-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>

        {{-- Ticket moyen --}}
        <div class="relative overflow-hidden rounded-xl bg-night-800 border border-white/5 p-5">
            <div class="text-xs font-semibold text-night-200 uppercase tracking-wider mb-2">Ticket moyen</div>
            <div class="text-2xl font-bold text-white">{{ number_format($todayStats->avg_ticket, 0, ',', ' ') }}</div>
            <div class="text-xs text-night-300 mt-0.5">FCFA / transaction</div>
            <div class="absolute top-4 right-4 w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                <svg class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
        </div>

        {{-- CA mois --}}
        <div class="relative overflow-hidden rounded-xl bg-night-800 border border-white/5 p-5">
            <div class="text-xs font-semibold text-night-200 uppercase tracking-wider mb-2">CA ce mois</div>
            <div class="text-2xl font-bold text-white">{{ number_format($monthTotal, 0, ',', ' ') }}</div>
            <div class="text-xs text-night-300 mt-0.5">FCFA depuis le 1er</div>
            <div class="absolute top-4 right-4 w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                <svg class="h-4 w-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ── Caisse + Crédits + Pertes ────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

        {{-- Statut caisse --}}
        <div class="rounded-xl bg-night-800 border p-4
            {{ $openSession ? 'border-emerald-500/25' : 'border-red-500/25' }}">
            <div class="flex items-center gap-2 mb-3">
                <span class="inline-block w-2 h-2 rounded-full {{ $openSession ? 'bg-emerald-400 shadow-emerald-400/60 shadow-sm' : 'bg-red-400' }}"></span>
                <span class="text-sm font-semibold {{ $openSession ? 'text-emerald-400' : 'text-red-400' }}">
                    Caisse {{ $openSession ? 'ouverte' : 'fermée' }}
                </span>
            </div>
            @if($openSession)
                <div class="text-xs text-night-200 space-y-1">
                    <div><span class="text-night-100 font-medium">{{ $openSession->register_name }}</span> — {{ $openSession->cashier_name }}</div>
                    <div class="text-night-200">Depuis {{ \Carbon\Carbon::parse($openSession->opened_at)->format('H:i') }}</div>
                    <div class="mt-2 font-semibold text-white">
                        Espèces : {{ number_format($cashSalesToday, 0, ',', ' ') }} FCFA
                    </div>
                </div>
                @can('open-cash-session')
                    <a href="{{ route('cash-sessions.index') }}"
                        class="mt-3 block text-xs text-center text-emerald-400 border border-emerald-500/25 rounded-lg py-1.5 hover:bg-emerald-500/10 transition-colors">
                        Gérer la session →
                    </a>
                @endcan
            @else
                <p class="text-xs text-night-200 mt-1">Aucune session ouverte.</p>
                @can('open-cash-session')
                    <a href="{{ route('cash-sessions.index') }}"
                        class="mt-3 block text-xs text-center text-white bg-neon-600 hover:bg-neon-500 rounded-lg py-2 transition-colors font-semibold">
                        Ouvrir la caisse
                    </a>
                @endcan
            @endif
        </div>

        {{-- Encours crédits --}}
        <div class="rounded-xl bg-night-800 border border-blue-500/20 p-4">
            <div class="text-xs font-semibold text-night-200 uppercase tracking-wider mb-2">Crédits clients</div>
            @if($creditStats && $creditStats->count > 0)
                <div class="text-xl font-bold text-blue-300">{{ number_format($creditStats->total, 0, ',', ' ') }} FCFA</div>
                <div class="text-xs text-night-200 mt-1">{{ $creditStats->count }} client(s) avec crédit</div>
                <a href="{{ route('customers.index') }}"
                    class="mt-3 block text-xs text-center text-blue-400 border border-blue-500/25 rounded-lg py-1.5 hover:bg-blue-500/10 transition-colors">
                    Voir les clients →
                </a>
            @else
                <div class="text-sm text-night-300 mt-2">Aucun encours.</div>
            @endif
        </div>

        {{-- Pertes --}}
        <div class="rounded-xl bg-night-800 border {{ $todayLosses->count > 0 ? 'border-amber-500/25' : 'border-white/5' }} p-4">
            <div class="text-xs font-semibold text-night-200 uppercase tracking-wider mb-2">Pertes / Casses</div>
            @if($todayLosses->count > 0)
                <div class="text-xl font-bold text-amber-400">{{ number_format($todayLosses->total_cost, 0, ',', ' ') }} FCFA</div>
                <div class="text-xs text-night-200 mt-1">{{ $todayLosses->count }} déclaration(s)</div>
                <a href="{{ route('losses.index') }}"
                    class="mt-3 block text-xs text-center text-amber-400 border border-amber-500/25 rounded-lg py-1.5 hover:bg-amber-500/10 transition-colors">
                    Voir les pertes →
                </a>
            @else
                <div class="text-sm text-night-300 mt-2">Aucune perte aujourd'hui.</div>
            @endif
        </div>
    </div>

    {{-- ── Graphique + Top produits ─────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

        {{-- Graphique 7 jours --}}
        <div class="rounded-xl bg-night-800 border border-white/5 p-5">
            <h3 class="text-sm font-semibold text-night-100 mb-4">CA — 7 derniers jours</h3>
            <div class="flex items-stretch gap-1.5 h-32">
                @foreach($weekChart as $day)
                    @php $pct = $weekMax > 0 ? ($day['total'] / $weekMax * 100) : 0; @endphp
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <div class="text-night-300 leading-none" style="font-size:9px">
                            {{ $day['total'] > 0 ? number_format($day['total']/1000, 0) . 'k' : '' }}
                        </div>
                        <div class="w-full flex-1 flex items-end">
                            <div class="w-full rounded-t transition-all"
                                style="height: {{ $day['total'] > 0 ? max($pct, 4) : 1 }}%; background: {{ $day['day'] === now()->toDateString() ? '#d4af37' : 'rgba(212,175,55,0.25)' }}">
                            </div>
                        </div>
                        <div class="text-night-300 leading-none" style="font-size:9px">{{ $day['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Top produits --}}
        <div class="rounded-xl bg-night-800 border border-white/5 p-5">
            <h3 class="text-sm font-semibold text-night-100 mb-3">Top produits aujourd'hui</h3>
            @if($topProducts->isEmpty())
                <p class="text-sm text-night-300">Aucune vente aujourd'hui.</p>
            @else
                @php $maxRevenue = $topProducts->max('revenue') ?: 1; @endphp
                <div class="space-y-2.5">
                    @foreach($topProducts as $product)
                        @php $share = round($product->revenue / $maxRevenue * 100); @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-night-200 truncate max-w-[160px]">{{ $product->product_name }}</span>
                                <span class="font-semibold text-night-50 ml-2 shrink-0">
                                    {{ number_format($product->revenue, 0, ',', ' ') }}
                                </span>
                            </div>
                            <div class="w-full bg-night-600 rounded-full h-1">
                                <div class="bg-neon-500 h-1 rounded-full transition-all" style="width: {{ $share }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ── Alertes stock + Ventes récentes ─────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

        {{-- Alertes stock --}}
        <div class="rounded-xl bg-night-800 border p-5 {{ $stockAlertCount > 0 ? 'border-red-500/25' : 'border-white/5' }}">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-night-100">Alertes stock</h3>
                @if($stockAlertCount > 0)
                    <span class="badge-danger">{{ $stockAlertCount }} produit(s)</span>
                @endif
            </div>
            @if($stockAlerts->isEmpty())
                <p class="text-sm text-emerald-400">✓ Tous les stocks satisfaisants.</p>
            @else
                <div class="space-y-2">
                    @foreach($stockAlerts as $alert)
                        <div class="flex items-center justify-between text-xs py-1.5 border-b border-white/4 last:border-0">
                            <span class="font-medium text-night-100">{{ $alert->name }}</span>
                            <div class="flex items-center gap-2">
                                <span class="text-night-300">{{ number_format($alert->stock_quantity, 2) }} {{ $alert->unit }}</span>
                                <span class="{{ $alert->status === 'rupture' ? 'badge-danger' : 'badge-warning' }}">
                                    {{ $alert->status === 'rupture' ? 'Rupture' : 'Bas' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($stockAlertCount > $stockAlerts->count())
                    <a href="{{ route('reports.stock') }}" class="mt-2 block text-xs text-center text-red-400 hover:text-red-300 transition-colors">
                        + {{ $stockAlertCount - $stockAlerts->count() }} autres alertes →
                    </a>
                @endif
            @endif
        </div>

        {{-- Ventes récentes --}}
        <div class="rounded-xl bg-night-800 border border-white/5 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-night-100">Ventes récentes</h3>
                <a href="{{ route('sales.index') }}" class="text-xs text-neon-400 hover:text-neon-300 transition-colors">Voir tout →</a>
            </div>
            <div class="space-y-0.5">
                @forelse($recentSales as $sale)
                    <div class="flex items-center justify-between py-1.5 border-b border-white/4 last:border-0 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-night-300 text-[10px]">{{ $sale->number }}</span>
                            <span class="text-night-300">{{ \Carbon\Carbon::parse($sale->created_at)->format('H:i') }}</span>
                            @if($sale->customer_name)
                                <span class="text-blue-400">{{ $sale->customer_name }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold {{ $sale->status === 'cancelled' ? 'line-through text-night-300' : 'text-night-50' }}">
                                {{ number_format($sale->total_amount, 0, ',', ' ') }}
                            </span>
                            <span class="{{ match($sale->status) { 'completed'=>'text-emerald-400','cancelled'=>'text-red-400',default=>'text-night-300' } }}">
                                {{ match($sale->status) { 'completed'=>'✓','cancelled'=>'✗',default=>'?' } }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-night-300">Aucune vente.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Accès rapides ─────────────────────────────────────────────── --}}
    <div class="border-t border-white/5 pt-4">
        <p class="text-xs font-bold text-night-300 uppercase tracking-widest mb-3">Accès rapides</p>
        <div class="flex flex-wrap gap-2">
            @can('create-sales')
                <a href="{{ route('pos.index') }}"
                    class="px-4 py-2 bg-gold-400 hover:bg-gold-300 text-night-900 text-sm font-bold rounded-lg transition-colors">
                    Ouvrir le POS
                </a>
            @endcan
            @can('view-reports')
                <a href="{{ route('reports.sales') }}"
                    class="px-4 py-2 bg-night-700 hover:bg-night-600 border border-white/8 text-night-200 hover:text-night-50 text-sm font-medium rounded-lg transition-colors">
                    Rapport ventes
                </a>
                <a href="{{ route('margins.index') }}"
                    class="px-4 py-2 bg-night-700 hover:bg-night-600 border border-white/8 text-night-200 hover:text-night-50 text-sm font-medium rounded-lg transition-colors">
                    Analyse marges
                </a>
            @endcan
            @can('manage-inventory')
                <a href="{{ route('inventories.index') }}"
                    class="px-4 py-2 bg-night-700 hover:bg-night-600 border border-white/8 text-night-200 hover:text-night-50 text-sm font-medium rounded-lg transition-colors">
                    Inventaire
                </a>
            @endcan
            @can('view-stock')
                <a href="{{ route('stock.index') }}"
                    class="px-4 py-2 bg-night-700 hover:bg-night-600 border border-white/8 text-night-200 hover:text-night-50 text-sm font-medium rounded-lg transition-colors">
                    Mouvements stock
                </a>
            @endcan
        </div>
    </div>

    <div class="text-right text-[10px] text-night-300">
        Mise à jour : {{ now()->format('H:i:s') }}
    </div>

</div>
