<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket {{ $sale->number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #111;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        .receipt {
            background: white;
            width: 80mm;
            max-width: 100%;
            padding: 12px 14px;
            border-radius: 4px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
        }

        .header {
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 1px dashed #bbb;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .header .sub {
            font-size: 11px;
            color: #555;
            margin-top: 2px;
            line-height: 1.5;
        }

        .meta {
            font-size: 11px;
            color: #444;
            margin-bottom: 10px;
            line-height: 1.7;
        }
        .meta .number {
            font-weight: bold;
            font-size: 12px;
            color: #111;
        }

        .items-header, .item {
            display: flex;
            align-items: baseline;
            gap: 4px;
            font-size: 11px;
            margin-bottom: 3px;
        }
        .items-header {
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .col-name  { flex: 1; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
        .col-qty   { width: 28px; text-align: center; flex-shrink: 0; }
        .col-up    { width: 50px; text-align: right; flex-shrink: 0; color: #666; }
        .col-total { width: 58px; text-align: right; flex-shrink: 0; font-weight: 600; }

        .items-section {
            border-top: 1px dashed #bbb;
            border-bottom: 1px dashed #bbb;
            padding: 8px 0;
            margin-bottom: 10px;
        }

        .totals { margin-bottom: 10px; }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #444;
            margin-bottom: 2px;
        }
        .total-row.discount { color: #b45309; }
        .total-row.grand {
            font-weight: bold;
            font-size: 14px;
            color: #111;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            margin-top: 5px;
        }

        .payment-box {
            background: #f9f9f9;
            border: 1px solid #e5e5e5;
            border-radius: 4px;
            padding: 7px 10px;
            margin-bottom: 10px;
            font-size: 11px;
        }
        .payment-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            color: #444;
        }
        .payment-row strong { color: #111; }
        .payment-row.change { color: #065f46; font-weight: bold; }
        .notes {
            font-size: 10px;
            color: #777;
            font-style: italic;
            margin-top: 4px;
            border-top: 1px dashed #ddd;
            padding-top: 4px;
        }

        .footer {
            text-align: center;
            font-size: 11px;
            color: #666;
            border-top: 1px dashed #bbb;
            padding-top: 8px;
            line-height: 1.6;
        }

        .actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
            justify-content: center;
        }
        .btn {
            padding: 7px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            font-family: sans-serif;
            font-weight: 600;
        }
        .btn-print { background: #111; color: #fff; }
        .btn-close { background: #e5e5e5; color: #333; }

        @media print {
            body { background: white; padding: 0; }
            .receipt { box-shadow: none; border-radius: 0; }
            .actions { display: none; }
            @page { margin: 4mm; size: 80mm auto; }
        }
    </style>
</head>
<body>
    <div class="receipt">

        <div class="header">
            <h1>{{ $settings->establishment_name ?? 'CAISSE PITCH' }}</h1>
            @if($settings->address || $settings->phone)
                <div class="sub">
                    @if($settings->address) {{ $settings->address }}<br> @endif
                    @if($settings->phone) Tél: {{ $settings->phone }} @endif
                </div>
            @endif
        </div>

        <div class="meta">
            <div class="number">N° {{ $sale->number }}</div>
            <div>{{ $sale->created_at->format('d/m/Y à H:i:s') }}</div>
            @if($sale->servedBy)
                <div>Serveur : {{ $sale->servedBy->name }}</div>
            @endif
            @if($sale->customer)
                <div>Client : {{ $sale->customer->name }}</div>
            @endif
        </div>

        <div class="items-section">
            <div class="items-header">
                <span class="col-name">Article</span>
                <span class="col-qty">Qté</span>
                <span class="col-up">P.U.</span>
                <span class="col-total">Total</span>
            </div>
            @foreach($sale->items as $item)
                <div class="item">
                    <span class="col-name">{{ $item->product_name }}</span>
                    <span class="col-qty">{{ (int) $item->quantity }}</span>
                    <span class="col-up">{{ number_format($item->unit_price, 0, ',', ' ') }}</span>
                    <span class="col-total">{{ number_format($item->total_price, 0, ',', ' ') }}</span>
                </div>
            @endforeach
        </div>

        <div class="totals">
            <div class="total-row">
                <span>Sous-total</span>
                <span>{{ number_format($sale->subtotal, 0, ',', ' ') }} {{ $settings->currency_code ?? 'FCFA' }}</span>
            </div>
            @if($sale->discount_amount > 0)
                <div class="total-row discount">
                    <span>Remise</span>
                    <span>−{{ number_format($sale->discount_amount, 0, ',', ' ') }} {{ $settings->currency_code ?? 'FCFA' }}</span>
                </div>
            @endif
            <div class="total-row grand">
                <span>TOTAL</span>
                <span>{{ number_format($sale->total_amount, 0, ',', ' ') }} {{ $settings->currency_code ?? 'FCFA' }}</span>
            </div>
        </div>

        <div class="payment-box">
            <div class="payment-row">
                <span>Mode de paiement</span>
                <strong>{{ $sale->payment_mode->label() }}</strong>
            </div>
            @if($sale->payment_mode->value === 'cash')
                @php
                    $amountGiven = session('ticket_amount_given_' . $sale->id, $sale->total_amount);
                    $change      = max(0, $amountGiven - $sale->total_amount);
                @endphp
            @endif
            @if($sale->notes)
                <div class="notes">Note : {{ $sale->notes }}</div>
            @endif
        </div>

        <div class="footer">
            @if($settings->ticket_message)
                {{ $settings->ticket_message }}
            @else
                Merci de votre visite !
            @endif
            @if($settings->ifu ?? null)
                <br><small>IFU : {{ $settings->ifu }}</small>
            @endif
        </div>

        <div class="actions">
            <button class="btn btn-print" onclick="window.print()">🖨 Imprimer</button>
            <button class="btn btn-close" onclick="window.close()">Fermer</button>
        </div>
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 400);
        });
    </script>
</body>
</html>
