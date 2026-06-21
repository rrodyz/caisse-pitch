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
        .report-title { font-size: 18px; font-weight: bold; color: #dc2626; }

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

        .badge { display: inline-block; padding: 1px 6px; border-radius: 10px; font-size: 8px; font-weight: bold; }
        .badge-loss  { background: #fee2e2; color: #dc2626; }
        .badge-break { background: #fef3c7; color: #d97706; }
        .badge-gift  { background: #ede9fe; color: #7c3aed; }

        .footer { margin-top: 14px; padding-top: 6px; border-top: 1px solid #e5e7eb; font-size: 8px; color: #9ca3af; text-align: right; }
    </style>
</head>
<body>

<div class="header">
    <div class="header-left">
        <h1>{{ $settings->establishment_name ?? config('app.name') }}</h1>
        <div class="meta">Rapport des pertes, casses & offerts — {{ $typeLabel }}</div>
        <div class="meta">Période : {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</div>
        <div class="meta">Généré le {{ now()->format('d/m/Y à H:i') }}</div>
    </div>
    <div class="header-right">
        <div class="report-title">RAPPORT PERTES</div>
    </div>
</div>

{{-- Résumé --}}
<div class="summary">
    <div class="card">
        <div class="card-label">Déclarations</div>
        <div class="card-value" style="color:#374151">{{ number_format($summary['count']) }}</div>
    </div>
    <div class="card">
        <div class="card-label">Coût total</div>
        <div class="card-value" style="color:#dc2626">{{ number_format($summary['cost'], 0, ',', ' ') }}</div>
        <div class="card-sub">FCFA</div>
    </div>
    <div class="card">
        <div class="card-label">Pertes</div>
        <div class="card-value" style="color:#dc2626">{{ number_format($summary['by_type']['loss']['cost'], 0, ',', ' ') }}</div>
        <div class="card-sub">{{ $summary['by_type']['loss']['cnt'] }} déc.</div>
    </div>
    <div class="card">
        <div class="card-label">Casses</div>
        <div class="card-value" style="color:#d97706">{{ number_format($summary['by_type']['break']['cost'], 0, ',', ' ') }}</div>
        <div class="card-sub">{{ $summary['by_type']['break']['cnt'] }} déc.</div>
    </div>
    <div class="card">
        <div class="card-label">Offerts</div>
        <div class="card-value" style="color:#7c3aed">{{ number_format($summary['by_type']['gift']['cost'], 0, ',', ' ') }}</div>
        <div class="card-sub">{{ $summary['by_type']['gift']['cnt'] }} déc.</div>
    </div>
</div>

{{-- Table --}}
<table class="data">
    <thead>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Produit</th>
            <th class="r">Quantité</th>
            <th class="r">P.U. (FCFA)</th>
            <th class="r">Coût total</th>
            <th>Motif</th>
            <th>Déclaré par</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
            <tr>
                <td style="white-space:nowrap">{{ \Carbon\Carbon::parse($row->created_at)->format('d/m/Y H:i') }}</td>
                <td>
                    @php
                        $cls = match($row->type) { 'loss'=>'badge-loss','break'=>'badge-break','gift'=>'badge-gift',default=>'' };
                        $lbl = match($row->type) { 'loss'=>'Perte','break'=>'Casse','gift'=>'Offert',default=>$row->type };
                    @endphp
                    <span class="badge {{ $cls }}">{{ $lbl }}</span>
                </td>
                <td><strong>{{ $row->product_name }}</strong></td>
                <td class="r">{{ number_format($row->quantity, 2) }} {{ $row->product_unit }}</td>
                <td class="r">{{ $row->unit_cost ? number_format($row->unit_cost, 0, ',', ' ') : '—' }}</td>
                <td class="r"><strong>{{ number_format($row->total_cost, 0, ',', ' ') }}</strong></td>
                <td style="max-width:120px">{{ $row->reason ?? '—' }}</td>
                <td>{{ $row->user_name ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="8" style="text-align:center;color:#9ca3af;padding:14px">Aucune déclaration sur cette période.</td></tr>
        @endforelse
    </tbody>
    @if($rows->isNotEmpty())
    <tfoot>
        <tr>
            <td colspan="5">TOTAL</td>
            <td class="r">{{ number_format($summary['cost'], 0, ',', ' ') }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
    @endif
</table>

<div class="footer">{{ $settings->establishment_name ?? config('app.name') }} — Rapport pertes {{ $typeLabel }} — {{ now()->format('d/m/Y H:i') }}</div>

</body>
</html>
