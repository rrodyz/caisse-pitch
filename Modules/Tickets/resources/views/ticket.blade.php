<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket {{ $sale->number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            background: #f3f4f6;
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        .ticket {
            background: white;
            width: 302px; /* 80mm ≈ 302px */
            padding: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.15);
        }
        .center   { text-align: center; }
        .bold     { font-weight: bold; }
        .line     { border-top: 1px dashed #999; margin: 6px 0; }
        .row      { display: flex; justify-content: space-between; }
        .row-item { display: flex; justify-content: space-between; gap: 4px; margin: 3px 0; }
        .item-name { flex: 1; }
        .item-qty  { width: 36px; text-align: center; }
        .item-pu   { width: 60px; text-align: right; }
        .item-tot  { width: 65px; text-align: right; font-weight: bold; }
        .total-row { display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; }
        .dim       { color: #666; }
        .no-print  { display: flex; gap: 8px; justify-content: center; margin-top: 20px; }
        .btn {
            padding: 8px 20px; border: none; border-radius: 4px;
            cursor: pointer; font-size: 13px; font-weight: bold;
        }
        .btn-print  { background: #4f46e5; color: white; }
        .btn-pdf    { background: #059669; color: white; }
        .btn-close  { background: #6b7280; color: white; }

        @media print {
            body { background: white; padding: 0; }
            .ticket { box-shadow: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div>
    <div class="ticket">
        {{-- En-tête établissement --}}
        <div class="center bold" style="font-size:14px">{{ $settings->establishment_name ?? config('app.name') }}</div>
        @if($settings->address)
            <div class="center dim">{{ $settings->address }}</div>
        @endif
        @if($settings->phone)
            <div class="center dim">Tél : {{ $settings->phone }}</div>
        @endif
        @if($settings->ifu)
            <div class="center dim">IFU : {{ $settings->ifu }}</div>
        @endif

        <div class="line"></div>

        {{-- Infos vente --}}
        <div class="row">
            <span class="dim">Ticket</span>
            <span class="bold">{{ $sale->number }}</span>
        </div>
        <div class="row">
            <span class="dim">Date</span>
            <span>{{ $sale->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="row">
            <span class="dim">Caissier</span>
            <span>{{ $sale->servedBy?->full_name ?? '—' }}</span>
        </div>
        @if($sale->cashSession?->cashRegister)
            <div class="row">
                <span class="dim">Caisse</span>
                <span>{{ $sale->cashSession->cashRegister->name }}</span>
            </div>
        @endif

        <div class="line"></div>

        {{-- En-têtes colonnes --}}
        <div class="row-item dim" style="font-size:10px;border-bottom:1px solid #ccc;padding-bottom:3px;margin-bottom:4px">
            <span class="item-name">DÉSIGNATION</span>
            <span class="item-qty">QTÉ</span>
            <span class="item-pu">PU</span>
            <span class="item-tot">TOTAL</span>
        </div>

        {{-- Articles --}}
        @foreach ($sale->items as $item)
            <div class="row-item">
                <span class="item-name">{{ $item->product_name }}</span>
                <span class="item-qty">{{ rtrim(rtrim(number_format($item->quantity,2),'0'),'.') }}</span>
                <span class="item-pu">{{ number_format($item->unit_price,0,',',' ') }}</span>
                <span class="item-tot">{{ number_format($item->total_price,0,',',' ') }}</span>
            </div>
        @endforeach

        <div class="line"></div>

        {{-- Totaux --}}
        <div class="row" style="margin-bottom:3px">
            <span class="dim">Sous-total</span>
            <span>{{ number_format($sale->subtotal,0,',',' ') }} FCFA</span>
        </div>
        @if($sale->discount_amount > 0)
            <div class="row" style="margin-bottom:3px">
                <span class="dim">Remise</span>
                <span>−{{ number_format($sale->discount_amount,0,',',' ') }} FCFA</span>
            </div>
        @endif
        <div class="line"></div>
        <div class="total-row" style="font-size:15px;margin:4px 0">
            <span>TOTAL</span>
            <span>{{ number_format($sale->total_amount,0,',',' ') }} FCFA</span>
        </div>
        <div class="line"></div>

        {{-- Paiement --}}
        <div class="row" style="margin-bottom:2px">
            <span class="dim">Mode</span>
            <span>{{ $sale->payment_mode->label() }}</span>
        </div>
        <div class="row">
            <span class="dim">Statut</span>
            <span>{{ $sale->payment_status === 'paid' ? 'Payé' : ($sale->payment_status === 'pending' ? 'En attente' : 'Partiel') }}</span>
        </div>

        @if($sale->notes)
            <div class="line"></div>
            <div class="dim" style="font-size:10px">Note : {{ $sale->notes }}</div>
        @endif

        <div class="line"></div>

        {{-- Pied de page --}}
        <div class="center dim" style="font-size:10px">
            @if($settings->ticket_message)
                {{ $settings->ticket_message }}
            @else
                Merci de votre visite !
            @endif
        </div>
        @if($settings->website ?? false)
            <div class="center dim" style="font-size:9px;margin-top:4px">{{ $settings->website }}</div>
        @endif
    </div>

    {{-- Boutons hors impression --}}
    <div class="no-print">
        <button class="btn btn-print" onclick="window.print()">Imprimer</button>
        <a class="btn btn-pdf" href="{{ route('tickets.pdf', $sale->id) }}" target="_blank">PDF</a>
        <button class="btn btn-close" onclick="window.close()">Fermer</button>
    </div>
</div>

<script>
    // Auto-print à l'ouverture (décommenter si souhaité)
    // window.addEventListener('load', () => { setTimeout(() => window.print(), 300); });
</script>
</body>
</html>
