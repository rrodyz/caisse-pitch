<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Courier New', Courier, monospace; font-size: 11px; color: #000; width: 74mm; }
        .center { text-align: center; }
        .right  { text-align: right; }
        .bold   { font-weight: bold; }
        .line-d { border-top: 1px dashed #000; margin: 4px 0; }
        .line-s { border-top: 1px solid #000; margin: 4px 0; }
        .row    { width: 100%; border-collapse: collapse; margin: 2px 0; }
        .row td { padding: 0; }
        .small  { font-size: 9px; }
        .title  { font-size: 13px; font-weight: bold; text-align: center; margin: 3px 0; }
        .section-title { font-weight: bold; margin: 4px 0 2px; font-size: 10px; text-transform: uppercase; }
        .gap-pos { color: #155724; }
        .gap-neg { color: #721c24; }
    </style>
</head>
<body>

{{-- En-tête --}}
<div class="center bold" style="font-size:13px">{{ $settings->establishment_name ?? config('app.name') }}</div>
@if($settings->address)<div class="center small">{{ $settings->address }}</div>@endif
@if($settings->phone)<div class="center small">Tél : {{ $settings->phone }}</div>@endif

<div class="line-s"></div>
<div class="title">*** RAPPORT Z ***</div>
<div class="center small">RAPPORT DE CLÔTURE</div>
<div class="line-s"></div>

{{-- Infos session --}}
<div class="section-title">Session</div>
<table class="row"><tr><td>Caisse</td><td class="right bold">{{ $session->cashRegister?->name }}</td></tr></table>
<table class="row"><tr><td>Ouverture</td><td class="right">{{ $session->opened_at->format('d/m/Y H:i') }}</td></tr></table>
<table class="row"><tr><td>Clôture</td><td class="right">{{ $session->closed_at?->format('d/m/Y H:i') ?? '—' }}</td></tr></table>
<table class="row"><tr><td>Durée</td><td class="right">{{ $session->duration() }}</td></tr></table>
<table class="row"><tr><td>Caissier</td><td class="right">{{ $session->openedBy?->full_name }}</td></tr></table>

<div class="line-d"></div>

{{-- Résumé ventes --}}
<div class="section-title">Ventes</div>
<table class="row"><tr><td>Complétées</td><td class="right bold">{{ $stats['sales_count'] }}</td></tr></table>
<table class="row"><tr><td>Annulées</td><td class="right">{{ $stats['cancelled_count'] }}</td></tr></table>

<div class="line-d"></div>

{{-- Par mode --}}
<div class="section-title">Par mode de paiement</div>
@foreach(['cash'=>'Espèces','card'=>'Carte','mobile_money'=>'Mobile Money','orange_money'=>'Orange Money','moov_money'=>'Moov Money','wave'=>'Wave','credit'=>'Crédit'] as $mode=>$label)
    @if($stats['by_mode'][$mode] > 0)
        <table class="row"><tr><td>{{ $label }}</td><td class="right">{{ number_format($stats['by_mode'][$mode],0,',',' ') }}</td></tr></table>
    @endif
@endforeach
@if($stats['total_discount'] > 0)
    <table class="row"><tr><td>Remises</td><td class="right">-{{ number_format($stats['total_discount'],0,',',' ') }}</td></tr></table>
@endif
<div class="line-s"></div>
<table class="row bold" style="font-size:13px"><tr><td>TOTAL</td><td class="right">{{ number_format($stats['total_sales'],0,',',' ') }} FCFA</td></tr></table>

<div class="line-d"></div>

{{-- Réconciliation --}}
<div class="section-title">Réconciliation espèces</div>
<table class="row"><tr><td>Fonds ouverture</td><td class="right">{{ number_format($session->opening_amount,0,',',' ') }}</td></tr></table>
<table class="row"><tr><td>+ Ventes espèces</td><td class="right">{{ number_format($stats['cash_sales'],0,',',' ') }}</td></tr></table>
<div class="line-s"></div>
<table class="row bold"><tr><td>= Total attendu</td><td class="right">{{ number_format($stats['expected_closing'],0,',',' ') }}</td></tr></table>
@if($session->closing_amount !== null)
    <table class="row"><tr><td>Total compté</td><td class="right">{{ number_format($session->closing_amount,0,',',' ') }}</td></tr></table>
    @php $g = $stats['gap'] ?? 0; @endphp
    <div class="line-s"></div>
    <table class="row bold {{ $g >= 0 ? 'gap-pos' : 'gap-neg' }}" style="font-size:12px">
        <tr><td>ÉCART</td><td class="right">{{ $g >= 0 ? '+' : '' }}{{ number_format($g,0,',',' ') }} FCFA {{ abs($g) < 100 ? 'OK' : '(!!)' }}</td></tr>
    </table>
@endif

{{-- Annulations --}}
@if(!empty($stats['cancelled_list']))
    <div class="line-d"></div>
    <div class="section-title">Annulations ({{ count($stats['cancelled_list']) }})</div>
    @foreach($stats['cancelled_list'] as $sale)
        <table class="row small"><tr>
            <td>{{ $sale->number }} {{ $sale->cancelled_at?->format('H:i') }}</td>
            <td class="right">{{ number_format($sale->total_amount,0,',',' ') }}</td>
        </tr></table>
    @endforeach
@endif

<div class="line-d"></div>

{{-- Signatures --}}
<div class="section-title" style="margin-bottom:16px">Signatures</div>
<div style="margin-bottom:14px">
    <div style="border-top:1px solid #000;padding-top:2px" class="small">Caissier : {{ $session->openedBy?->full_name }}</div>
</div>
<div>
    <div style="border-top:1px solid #000;padding-top:2px" class="small">Superviseur : ___________________</div>
</div>

<div class="line-d"></div>
<div class="center small">{{ now()->format('d/m/Y H:i') }} — Rapport Z</div>

</body>
</html>
