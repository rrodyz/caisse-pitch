<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #1a1a2e; }
        h1 { font-size: 14px; font-weight: bold; }
        h2 { font-size: 11px; font-weight: bold; margin-bottom: 4px; }

        .header { padding: 10px 0 8px; border-bottom: 2px solid #1a1a2e; margin-bottom: 10px; }
        .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
        .meta { font-size: 9px; color: #555; margin-top: 3px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th { background: #1a1a2e; color: #fff; padding: 5px 6px; text-align: left; font-size: 9px; text-transform: uppercase; }
        th.r { text-align: right; }
        td { padding: 4px 6px; border-bottom: 1px solid #e5e7eb; font-size: 9px; vertical-align: middle; }
        td.r { text-align: right; }
        tr:nth-child(even) td { background: #f9fafb; }

        .badge { display: inline-block; padding: 1px 6px; border-radius: 9px; font-size: 8px; font-weight: bold; }
        .ok      { background: #d1fae5; color: #065f46; }
        .bas     { background: #fef3c7; color: #92400e; }
        .rupture { background: #fee2e2; color: #991b1b; }

        .summary { display: table; width: 100%; margin-bottom: 12px; }
        .summary-card { display: table-cell; width: 25%; padding: 8px 10px; border: 1px solid #e5e7eb; border-radius: 4px; text-align: center; }
        .summary-label { font-size: 8px; color: #6b7280; margin-bottom: 2px; }
        .summary-value { font-size: 14px; font-weight: bold; color: #1a1a2e; }

        tfoot td { background: #1a1a2e; color: #fff; font-weight: bold; padding: 5px 6px; }
        tfoot td.r { text-align: right; }

        .delta-pos { color: #065f46; font-weight: bold; }
        .delta-neg { color: #991b1b; font-weight: bold; }

        .footer { margin-top: 14px; padding-top: 6px; border-top: 1px solid #e5e7eb; font-size: 8px; color: #9ca3af; text-align: right; }
    </style>
</head>
<body>

<div class="header">
    <div class="header-top">
        <div>
            <h1>{{ $settings->establishment_name ?? config('app.name') }}</h1>
            <div class="meta">{{ $title }} — généré le {{ now()->format('d/m/Y à H:i') }}</div>
            @if($search)
                <div class="meta">Filtre : "{{ $search }}"</div>
            @endif
            @if($dateFrom || $dateTo)
                <div class="meta">Période : {{ $dateFrom ?: '…' }} → {{ $dateTo ?: '…' }}</div>
            @endif
        </div>
        <div style="text-align:right">
            <div style="font-size:18px;font-weight:bold;color:#6366f1">RAPPORT STOCK</div>
        </div>
    </div>
</div>

{{-- ── VALORISATION ── --}}
@if($view === 'valuation')

    {{-- Cartes résumé --}}
    <div class="summary">
        <div class="summary-card">
            <div class="summary-label">Valeur totale</div>
            <div class="summary-value">{{ number_format($data['summary']['total_value'], 0, ',', ' ') }}</div>
            <div class="meta">FCFA</div>
        </div>
        <div class="summary-card" style="margin-left:6px">
            <div class="summary-label">Références actives</div>
            <div class="summary-value">{{ $data['summary']['total_products'] }}</div>
        </div>
        <div class="summary-card" style="margin-left:6px">
            <div class="summary-label">Stock bas</div>
            <div class="summary-value" style="color:#92400e">{{ $data['summary']['low_stock_count'] }}</div>
        </div>
        <div class="summary-card" style="margin-left:6px">
            <div class="summary-label">Ruptures</div>
            <div class="summary-value" style="color:#991b1b">{{ $data['summary']['out_stock_count'] }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Catégorie</th>
                <th>Unité</th>
                <th class="r">Stock</th>
                <th class="r">Mini</th>
                <th class="r">Px achat</th>
                <th class="r">Valeur (FCFA)</th>
                <th style="text-align:center">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['rows'] as $row)
                <tr>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->category_name ?? '—' }}</td>
                    <td>{{ $row->unit }}</td>
                    <td class="r">{{ number_format($row->stock_quantity, 2) }}</td>
                    <td class="r" style="color:#6b7280">{{ number_format($row->min_stock, 2) }}</td>
                    <td class="r" style="color:#6b7280">{{ number_format($row->purchase_price, 0, ',', ' ') }}</td>
                    <td class="r"><strong>{{ number_format($row->stock_value, 0, ',', ' ') }}</strong></td>
                    <td style="text-align:center">
                        <span class="badge {{ $row->stock_status }}">
                            {{ $row->stock_status === 'ok' ? 'OK' : ($row->stock_status === 'bas' ? 'Bas' : 'Rupture') }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;color:#9ca3af;padding:12px">Aucun produit.</td></tr>
            @endforelse
        </tbody>
        @if(isset($data['rows']) && $data['rows']->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="6">TOTAL</td>
                <td class="r">{{ number_format($data['summary']['total_value'], 0, ',', ' ') }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>
@endif

{{-- ── MOUVEMENTS ── --}}
@if($view === 'movements')
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Produit</th>
                <th>Type</th>
                <th class="r">Avant</th>
                <th class="r">Mouvement</th>
                <th class="r">Après</th>
                <th>Notes</th>
                <th>Utilisateur</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['rows'] as $row)
                @php $delta = $row->quantity_after - $row->quantity_before; @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $row->product_name }}</td>
                    <td>{{ str_replace('_', ' ', $row->type) }}</td>
                    <td class="r" style="color:#6b7280">{{ number_format($row->quantity_before, 3) }}</td>
                    <td class="r {{ $delta >= 0 ? 'delta-pos' : 'delta-neg' }}">
                        {{ $delta > 0 ? '+' : '' }}{{ number_format($delta, 3) }}
                    </td>
                    <td class="r">{{ number_format($row->quantity_after, 3) }}</td>
                    <td style="color:#6b7280;max-width:120px">{{ $row->notes ?? '—' }}</td>
                    <td>{{ $row->user_name ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;color:#9ca3af;padding:12px">Aucun mouvement.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="font-size:8px;color:#9ca3af">Limité aux 500 derniers mouvements.</div>
@endif

{{-- ── ALERTES ── --}}
@if($view === 'alerts')
    @if($data['rows']->isEmpty())
        <div style="text-align:center;padding:30px;color:#065f46;font-size:12px;font-weight:bold">
            Aucune alerte — tous les niveaux sont satisfaisants.
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Catégorie</th>
                    <th>Unité</th>
                    <th class="r">Stock actuel</th>
                    <th class="r">Stock mini</th>
                    <th class="r">Manquant</th>
                    <th style="text-align:center">Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['rows'] as $row)
                    <tr>
                        <td>{{ $row->name }}</td>
                        <td>{{ $row->category_name ?? '—' }}</td>
                        <td>{{ $row->unit }}</td>
                        <td class="r {{ $row->stock_quantity <= 0 ? 'delta-neg' : '' }}" style="{{ $row->stock_quantity > 0 ? 'color:#92400e;font-weight:bold' : '' }}">
                            {{ number_format($row->stock_quantity, 2) }}
                        </td>
                        <td class="r" style="color:#6b7280">{{ number_format($row->min_stock, 2) }}</td>
                        <td class="r delta-neg">{{ number_format($row->shortage, 2) }}</td>
                        <td style="text-align:center">
                            <span class="badge {{ $row->stock_quantity <= 0 ? 'rupture' : 'bas' }}">
                                {{ $row->stock_quantity <= 0 ? 'Rupture' : 'Stock bas' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endif

<div class="footer">{{ $settings->establishment_name ?? config('app.name') }} — {{ $title }} — {{ now()->format('d/m/Y H:i') }}</div>

</body>
</html>
