<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #1a1a2e; }

        .header { padding: 10px 0 8px; border-bottom: 2px solid #1a1a2e; margin-bottom: 10px; display: table; width: 100%; }
        .header-left  { display: table-cell; vertical-align: top; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        h1 { font-size: 14px; font-weight: bold; }
        .meta { font-size: 9px; color: #555; margin-top: 3px; }
        .report-title { font-size: 18px; font-weight: bold; color: #6366f1; }

        .summary { display: table; width: 100%; margin-bottom: 12px; border-collapse: separate; border-spacing: 4px; }
        .card { display: table-cell; padding: 8px 10px; border: 1px solid #e5e7eb; border-radius: 4px; text-align: center; }
        .card-label { font-size: 8px; color: #6b7280; margin-bottom: 2px; }
        .card-value { font-size: 15px; font-weight: bold; }
        .card-sub   { font-size: 8px; color: #9ca3af; }

        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #1a1a2e; color: #fff; padding: 5px 7px; text-align: left; font-size: 9px; text-transform: uppercase; }
        table.data th.r { text-align: right; }
        table.data td { padding: 4px 7px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
        table.data td.r { text-align: right; }
        table.data tr:nth-child(even) td { background: #f9fafb; }
        table.data tfoot td { background: #1a1a2e; color: #fff; font-weight: bold; padding: 5px 7px; }
        table.data tfoot td.r { text-align: right; }

        .bar-wrap { display: inline-block; width: 40px; height: 5px; background: #e5e7eb; border-radius: 3px; vertical-align: middle; margin-right: 3px; }
        .bar-fill  { height: 5px; background: #6366f1; border-radius: 3px; }

        .footer { margin-top: 14px; padding-top: 6px; border-top: 1px solid #e5e7eb; font-size: 8px; color: #9ca3af; text-align: right; }
    </style>
</head>
<body>

<div class="header">
    <div class="header-left">
        <h1>{{ $settings->establishment_name ?? config('app.name') }}</h1>
        <div class="meta">Rapport des ventes — {{ $groupLabel }}</div>
        <div class="meta">Période : {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</div>
        <div class="meta">Généré le {{ now()->format('d/m/Y à H:i') }}</div>
    </div>
    <div class="header-right">
        <div class="report-title">RAPPORT VENTES</div>
    </div>
</div>

{{-- Résumé --}}
<div class="summary">
    <div class="card">
        <div class="card-label">Transactions</div>
        <div class="card-value" style="color:#6366f1">{{ number_format($summary['count']) }}</div>
    </div>
    <div class="card">
        <div class="card-label">CA total</div>
        <div class="card-value" style="color:#059669">{{ number_format($summary['total'], 0, ',', ' ') }}</div>
        <div class="card-sub">FCFA</div>
    </div>
    <div class="card">
        <div class="card-label">Remises totales</div>
        <div class="card-value" style="color:#d97706">{{ number_format($summary['discounts'], 0, ',', ' ') }}</div>
        <div class="card-sub">FCFA</div>
    </div>
    <div class="card">
        <div class="card-label">Ticket moyen</div>
        <div class="card-value" style="color:#374151">{{ number_format($summary['avg_ticket'], 0, ',', ' ') }}</div>
        <div class="card-sub">FCFA</div>
    </div>
    <div class="card">
        <div class="card-label">Annulations</div>
        <div class="card-value" style="color:#dc2626">{{ number_format($summary['cancelled_count']) }}</div>
    </div>
</div>

{{-- Table --}}
<table class="data">
    <thead>
        <tr>
            <th>{{ match($groupBy) { 'day'=>'Date','product'=>'Produit','category'=>'Catégorie','payment_mode'=>'Mode paiement',default=>'Libellé' } }}</th>
            @if(in_array($groupBy, ['day','payment_mode']))
                <th class="r">Transactions</th>
            @else
                <th class="r">Qté</th>
            @endif
            <th class="r">CA (FCFA)</th>
            @if($groupBy === 'day')
                <th class="r">Remises</th>
                <th class="r">Ticket moy.</th>
            @endif
            <th class="r">% CA</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
            @php $share = round($row->total / $grandTotal * 100, 1); @endphp
            <tr>
                <td>{{ $row->label }}</td>
                @if(in_array($groupBy, ['day','payment_mode']))
                    <td class="r">{{ number_format($row->count) }}</td>
                @else
                    <td class="r">{{ number_format($row->qty ?? 0) }}</td>
                @endif
                <td class="r"><strong>{{ number_format($row->total, 0, ',', ' ') }}</strong></td>
                @if($groupBy === 'day')
                    <td class="r" style="color:#d97706">{{ number_format($row->discounts, 0, ',', ' ') }}</td>
                    <td class="r" style="color:#6b7280">{{ number_format($row->avg_ticket, 0, ',', ' ') }}</td>
                @endif
                <td class="r">
                    <span class="bar-wrap"><span class="bar-fill" style="width:{{ min($share, 100) }}%"></span></span>
                    {{ $share }}%
                </td>
            </tr>
        @empty
            <tr><td colspan="6" style="text-align:center;color:#9ca3af;padding:14px">Aucune vente sur cette période.</td></tr>
        @endforelse
    </tbody>
    @if($rows->isNotEmpty())
    <tfoot>
        <tr>
            <td>TOTAL</td>
            <td class="r">{{ number_format($summary['count']) }}</td>
            <td class="r">{{ number_format($summary['total'], 0, ',', ' ') }}</td>
            @if($groupBy === 'day')
                <td class="r">{{ number_format($summary['discounts'], 0, ',', ' ') }}</td>
                <td class="r">{{ number_format($summary['avg_ticket'], 0, ',', ' ') }}</td>
            @endif
            <td class="r">100%</td>
        </tr>
    </tfoot>
    @endif
</table>

<div class="footer">{{ $settings->establishment_name ?? config('app.name') }} — Rapport ventes {{ $groupLabel }} — {{ now()->format('d/m/Y H:i') }}</div>

</body>
</html>
