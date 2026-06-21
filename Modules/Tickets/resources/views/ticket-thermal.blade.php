<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            width: 74mm;
        }
        .center   { text-align: center; }
        .right    { text-align: right; }
        .bold     { font-weight: bold; }
        .line-d   { border-top: 1px dashed #000; margin: 4px 0; }
        .line-s   { border-top: 1px solid #000; margin: 4px 0; }
        .row      { display: flex; justify-content: space-between; margin: 1px 0; }
        table     { width: 100%; border-collapse: collapse; }
        th, td    { font-size: 10px; padding: 1px 2px; }
        th        { border-bottom: 1px solid #000; text-align: left; }
        td.r      { text-align: right; }
        td.c      { text-align: center; }
        .total-row { font-size: 14px; font-weight: bold; }
        .small    { font-size: 9px; }
    </style>
</head>
<body>

{{-- En-tête --}}
<div class="center bold" style="font-size:14px">{{ $settings->establishment_name ?? config('app.name') }}</div>
@if($settings->address)
    <div class="center small">{{ $settings->address }}</div>
@endif
@if($settings->phone)
    <div class="center small">Tél : {{ $settings->phone }}</div>
@endif
@if($settings->ifu)
    <div class="center small">IFU : {{ $settings->ifu }}</div>
@endif

<div class="line-d"></div>

<div class="row"><span>Ticket :</span><span class="bold">{{ $sale->number }}</span></div>
<div class="row"><span>Date :</span><span>{{ $sale->created_at->format('d/m/Y H:i') }}</span></div>
<div class="row"><span>Caissier :</span><span>{{ $sale->servedBy?->full_name ?? '—' }}</span></div>
@if($sale->cashSession?->cashRegister)
    <div class="row"><span>Caisse :</span><span>{{ $sale->cashSession->cashRegister->name }}</span></div>
@endif

<div class="line-d"></div>

{{-- Articles --}}
<table>
    <thead>
        <tr>
            <th style="width:40%">ARTICLE</th>
            <th class="c" style="width:12%">QTÉ</th>
            <th class="r" style="width:22%">P.U.</th>
            <th class="r" style="width:26%">TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($sale->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td class="c">{{ rtrim(rtrim(number_format($item->quantity,2),'0'),'.') }}</td>
                <td class="r">{{ number_format($item->unit_price,0,',',' ') }}</td>
                <td class="r bold">{{ number_format($item->total_price,0,',',' ') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="line-d"></div>

{{-- Totaux --}}
<div class="row"><span>Sous-total</span><span>{{ number_format($sale->subtotal,0,',',' ') }}</span></div>
@if($sale->discount_amount > 0)
    <div class="row"><span>Remise</span><span>-{{ number_format($sale->discount_amount,0,',',' ') }}</span></div>
@endif
<div class="line-s"></div>
<div class="row total-row">
    <span>TOTAL</span>
    <span>{{ number_format($sale->total_amount,0,',',' ') }} FCFA</span>
</div>
<div class="line-d"></div>

{{-- Paiement --}}
<div class="row"><span>Mode paiement</span><span>{{ $sale->payment_mode->label() }}</span></div>
<div class="row">
    <span>Statut</span>
    <span>{{ $sale->payment_status === 'paid' ? 'Payé' : ($sale->payment_status === 'pending' ? 'En attente' : 'Partiel') }}</span>
</div>

@if($sale->notes)
    <div class="line-d"></div>
    <div class="small">Note : {{ $sale->notes }}</div>
@endif

<div class="line-d"></div>

{{-- Pied de page --}}
<div class="center small">
    {{ $settings->ticket_message ?? 'Merci de votre visite !' }}
</div>
@if($settings->website ?? false)
    <div class="center small">{{ $settings->website }}</div>
@endif
<div class="center small" style="margin-top:4px">*** {{ $sale->number }} ***</div>

</body>
</html>
