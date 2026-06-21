<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #1a1a2e; }

        .header { padding: 10px 0 8px; border-bottom: 2px solid #1a1a2e; margin-bottom: 12px; display: table; width: 100%; }
        .header-left  { display: table-cell; vertical-align: top; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        h1 { font-size: 14px; font-weight: bold; }
        .meta { font-size: 9px; color: #555; margin-top: 3px; }
        .report-title { font-size: 18px; font-weight: bold; color: #6366f1; }

        .summary { display: table; width: 100%; margin-bottom: 14px; border-collapse: separate; border-spacing: 6px; }
        .card { display: table-cell; padding: 10px; border: 1px solid #e5e7eb; border-radius: 6px; text-align: center; }
        .card-label { font-size: 9px; color: #6b7280; margin-bottom: 3px; }
        .card-value { font-size: 18px; font-weight: bold; }
        .card-sub   { font-size: 8px; color: #9ca3af; }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.data th { background: #1a1a2e; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
        table.data th.r { text-align: right; }
        table.data td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        table.data td.r { text-align: right; }
        table.data tr:nth-child(even) td { background: #f9fafb; }
        table.data tfoot td { background: #1a1a2e; color: #fff; font-weight: bold; padding: 6px 8px; }
        table.data tfoot td.r { text-align: right; }

        .bar-wrap { display: inline-block; width: 60px; height: 6px; background: #e5e7eb; border-radius: 3px; vertical-align: middle; margin-right: 4px; }
        .bar-fill  { height: 6px; background: #6366f1; border-radius: 3px; }

        .footer { margin-top: 16px; padding-top: 6px; border-top: 1px solid #e5e7eb; font-size: 8px; color: #9ca3af; text-align: right; }
    </style>
</head>
<body>

<div class="header">
    <div class="header-left">
        <h1>{{ $settings->establishment_name ?? config('app.name') }}</h1>
        <div class="meta">Rapport des paiements</div>
        <div class="meta">Période : {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</div>
        <div class="meta">Généré le {{ now()->format('d/m/Y à H:i') }}</div>
    </div>
    <div class="header-right">
        <div class="report-title">PAIEMENTS</div>
    </div>
</div>

{{-- Résumé --}}
<div class="summary">
    <div class="card">
        <div class="card-label">Transactions</div>
        <div class="card-value" style="color:#6366f1">{{ number_format($totals->cnt ?? 0) }}</div>
    </div>
    <div class="card">
        <div class="card-label">CA total</div>
        <div class="card-value" style="color:#059669">{{ number_format($totals->total ?? 0, 0, ',', ' ') }}</div>
        <div class="card-sub">FCFA</div>
    </div>
    <div class="card">
        <div class="card-label">Modes actifs</div>
        <div class="card-value" style="color:#374151">{{ $byMode->count() }}</div>
    </div>
</div>

{{-- Table par mode --}}
<table class="data">
    <thead>
        <tr>
            <th>Mode de paiement</th>
            <th class="r">Transactions</th>
            <th class="r">Montant (FCFA)</th>
            <th class="r">Ticket moyen</th>
            <th class="r">% CA</th>
        </tr>
    </thead>
    <tbody>
        @php $grandTotal = $totals->total ?? 1; @endphp
        @forelse($byMode as $row)
            @php $share = round($row->total / $grandTotal * 100, 1); @endphp
            <tr>
                <td><strong>{{ $row->label }}</strong></td>
                <td class="r">{{ number_format($row->cnt) }}</td>
                <td class="r"><strong>{{ number_format($row->total, 0, ',', ' ') }}</strong></td>
                <td class="r" style="color:#6b7280">{{ number_format($row->avg_ticket, 0, ',', ' ') }}</td>
                <td class="r">
                    <span class="bar-wrap"><span class="bar-fill" style="width:{{ min($share, 100) }}%"></span></span>
                    {{ $share }}%
                </td>
            </tr>
        @empty
            <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:14px">Aucune vente sur cette période.</td></tr>
        @endforelse
    </tbody>
    @if($byMode->isNotEmpty())
    <tfoot>
        <tr>
            <td>TOTAL</td>
            <td class="r">{{ number_format($totals->cnt ?? 0) }}</td>
            <td class="r">{{ number_format($totals->total ?? 0, 0, ',', ' ') }}</td>
            <td class="r">—</td>
            <td class="r">100%</td>
        </tr>
    </tfoot>
    @endif
</table>

<div class="footer">{{ $settings->establishment_name ?? config('app.name') }} — Rapport paiements — {{ now()->format('d/m/Y H:i') }}</div>

</body>
</html>
