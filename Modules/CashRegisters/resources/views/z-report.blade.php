<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-night-50 leading-tight">
                Rapport Z — Session #{{ $session->id }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('cash-sessions.pdf', $session->id) }}" target="_blank"
                    class="px-4 py-2 bg-neon-600 text-white text-sm font-semibold rounded-md hover:bg-neon-500">
                    Télécharger PDF
                </a>
                <button onclick="window.print()"
                    class="px-4 py-2 bg-night-600 text-night-100 text-sm font-semibold rounded-md hover:bg-night-500 border border-white/10">
                    Imprimer
                </button>
                <a href="{{ route('cash-sessions.index') }}"
                    class="px-4 py-2 border border-white/10 text-night-200 text-sm font-medium rounded-md hover:bg-night-700">
                    ← Sessions
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 print:py-0">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-night-800 border border-white/5 shadow-xl sm:rounded-xl print:shadow-none">

                {{-- ── En-tête établissement ──────────────────────────────── --}}
                <div class="p-6 border-b border-white/8 text-center">
                    <h1 class="text-2xl font-bold text-night-50">{{ $settings->establishment_name ?? config('app.name') }}</h1>
                    @if($settings->address)
                        <p class="text-night-300 text-sm">{{ $settings->address }}</p>
                    @endif
                    @if($settings->phone)
                        <p class="text-night-300 text-sm">Tél : {{ $settings->phone }}</p>
                    @endif
                    <p class="text-lg font-bold text-neon-400 mt-3">RAPPORT DE CLÔTURE (Rapport Z)</p>
                </div>

                <div class="p-6 space-y-6">

                    {{-- ── Infos session ──────────────────────────────────── --}}
                    <section>
                        <h2 class="text-base font-bold text-night-100 mb-3 border-b border-white/8 pb-1">
                            Informations session
                        </h2>
                        <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-night-300">Caisse</span>
                                <span class="font-medium text-night-100">{{ $session->cashRegister?->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-night-300">Statut</span>
                                <span class="font-medium {{ $session->isOpen() ? 'text-emerald-400' : 'text-night-200' }}">
                                    {{ $session->status->label() }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-night-300">Ouverture</span>
                                <span class="text-night-100">{{ $session->opened_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-night-300">Clôture</span>
                                <span class="text-night-100">{{ $session->closed_at?->format('d/m/Y H:i') ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-night-300">Durée</span>
                                <span class="text-night-100">{{ $session->duration() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-night-300">Caissier</span>
                                <span class="text-night-100">{{ $session->openedBy?->full_name }}</span>
                            </div>
                            @if($session->closedBy)
                                <div class="flex justify-between">
                                    <span class="text-night-300">Clôturé par</span>
                                    <span class="text-night-100">{{ $session->closedBy->full_name }}</span>
                                </div>
                            @endif
                        </div>
                    </section>

                    {{-- ── Résumé ventes ──────────────────────────────────── --}}
                    <section>
                        <h2 class="text-base font-bold text-night-100 mb-3 border-b border-white/8 pb-1">
                            Résumé des ventes
                        </h2>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold text-emerald-300">{{ $stats['sales_count'] }}</div>
                                <div class="text-xs text-emerald-400">Ventes complétées</div>
                            </div>
                            <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold text-red-300">{{ $stats['cancelled_count'] }}</div>
                                <div class="text-xs text-red-400">Ventes annulées</div>
                            </div>
                        </div>

                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-night-700">
                                    <th class="px-3 py-2 text-left font-medium text-night-200">Mode de paiement</th>
                                    <th class="px-3 py-2 text-right font-medium text-night-200">Montant (FCFA)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @foreach ([
                                    'cash'         => 'Espèces',
                                    'card'         => 'Carte bancaire',
                                    'mobile_money' => 'Mobile Money',
                                    'orange_money' => 'Orange Money',
                                    'moov_money'   => 'Moov Money',
                                    'wave'         => 'Wave',
                                    'credit'       => 'Crédit client',
                                ] as $mode => $label)
                                    @if($stats['by_mode'][$mode] > 0)
                                        <tr>
                                            <td class="px-3 py-2 text-night-200">{{ $label }}</td>
                                            <td class="px-3 py-2 text-right font-medium text-night-100">{{ number_format($stats['by_mode'][$mode], 0, ',', ' ') }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                                @if($stats['total_discount'] > 0)
                                    <tr>
                                        <td class="px-3 py-2 text-amber-400">Remises accordées</td>
                                        <td class="px-3 py-2 text-right text-amber-400">−{{ number_format($stats['total_discount'], 0, ',', ' ') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr class="bg-neon-600/15 border-t border-neon-500/30 font-bold text-base">
                                    <td class="px-3 py-3 text-neon-300">TOTAL ENCAISSÉ</td>
                                    <td class="px-3 py-3 text-right text-neon-300">{{ number_format($stats['total_sales'], 0, ',', ' ') }} FCFA</td>
                                </tr>
                            </tfoot>
                        </table>
                    </section>

                    {{-- ── Réconciliation caisse ──────────────────────────── --}}
                    <section>
                        <h2 class="text-base font-bold text-night-100 mb-3 border-b border-white/8 pb-1">
                            Réconciliation espèces
                        </h2>
                        <table class="min-w-full text-sm">
                            <tbody class="divide-y divide-white/5">
                                <tr>
                                    <td class="px-3 py-2 text-night-300">Fonds d'ouverture</td>
                                    <td class="px-3 py-2 text-right text-night-100">{{ number_format($session->opening_amount, 0, ',', ' ') }} FCFA</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 text-night-300">+ Ventes espèces</td>
                                    <td class="px-3 py-2 text-right text-night-100">{{ number_format($stats['cash_sales'], 0, ',', ' ') }} FCFA</td>
                                </tr>
                                <tr class="bg-night-700 font-semibold">
                                    <td class="px-3 py-2 text-night-100">= Total attendu</td>
                                    <td class="px-3 py-2 text-right text-night-50">{{ number_format($stats['expected_closing'], 0, ',', ' ') }} FCFA</td>
                                </tr>
                                @if($session->closing_amount !== null)
                                    <tr>
                                        <td class="px-3 py-2 text-night-300">Montant compté</td>
                                        <td class="px-3 py-2 text-right text-night-100">{{ number_format($session->closing_amount, 0, ',', ' ') }} FCFA</td>
                                    </tr>
                                    @php $gap = $stats['gap'] ?? 0; @endphp
                                    <tr class="{{ $gap < 0 ? 'bg-red-500/10 text-red-300' : 'bg-emerald-500/10 text-emerald-300' }} font-bold text-base">
                                        <td class="px-3 py-3">Écart</td>
                                        <td class="px-3 py-3 text-right">
                                            {{ $gap >= 0 ? '+' : '' }}{{ number_format($gap, 0, ',', ' ') }} FCFA
                                            {{ abs($gap) < 100 ? '✓' : '' }}
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        @if($session->notes_closing)
                            <p class="mt-3 text-sm text-night-300 bg-night-700 rounded p-2">
                                Note clôture : {{ $session->notes_closing }}
                            </p>
                        @endif
                    </section>

                    {{-- ── Annulations ────────────────────────────────────── --}}
                    @if(!empty($stats['cancelled_list']))
                        <section>
                            <h2 class="text-base font-bold text-red-300 mb-3 border-b border-red-500/30 pb-1">
                                Ventes annulées ({{ count($stats['cancelled_list']) }})
                            </h2>
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-red-500/10">
                                        <th class="px-3 py-2 text-left font-medium text-night-200">N°</th>
                                        <th class="px-3 py-2 text-left font-medium text-night-200">Heure</th>
                                        <th class="px-3 py-2 text-right font-medium text-night-200">Montant</th>
                                        <th class="px-3 py-2 text-left font-medium text-night-200">Motif</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    @foreach($stats['cancelled_list'] as $sale)
                                        <tr>
                                            <td class="px-3 py-2 font-mono text-xs text-night-100">{{ $sale->number }}</td>
                                            <td class="px-3 py-2 text-night-300 text-xs">{{ $sale->cancelled_at?->format('H:i') }}</td>
                                            <td class="px-3 py-2 text-right text-red-400">{{ number_format($sale->total_amount, 0, ',', ' ') }}</td>
                                            <td class="px-3 py-2 text-night-300 text-xs">{{ $sale->cancel_reason ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </section>
                    @endif

                    {{-- ── Signatures ─────────────────────────────────────── --}}
                    <section class="border-t border-white/8 pt-4">
                        <div class="grid grid-cols-2 gap-8 text-sm text-night-300">
                            <div>
                                <div class="font-medium text-night-200 mb-6">Caissier</div>
                                <div class="border-b border-white/20 mb-1"></div>
                                <div>{{ $session->openedBy?->full_name }}</div>
                            </div>
                            <div>
                                <div class="font-medium text-night-200 mb-6">Superviseur / Gérant</div>
                                <div class="border-b border-white/20 mb-1"></div>
                                <div>&nbsp;</div>
                            </div>
                        </div>
                        <div class="text-center text-xs text-night-400 mt-6">
                            Généré le {{ now()->format('d/m/Y à H:i') }} — {{ config('app.name') }}
                        </div>
                    </section>

                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            nav, header, .no-print { display: none !important; }
        }
    </style>
</x-app-layout>
