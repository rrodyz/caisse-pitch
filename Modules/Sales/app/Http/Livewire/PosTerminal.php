<?php

namespace Modules\Sales\app\Http\Livewire;

use Illuminate\Support\Str;
use Livewire\Component;
use Modules\CashRegisters\app\Models\CashSession;
use Modules\CashRegisters\app\Services\CashSessionService;
use Modules\Sales\app\Models\Customer;
use Modules\Sales\app\Models\PaymentTransaction;
use Modules\Categories\app\Models\Category;
use Modules\Products\app\Models\Product;
use Modules\Sales\app\Enums\PaymentMode;
use Modules\Sales\app\Services\SaleService;
use Modules\Sales\app\Services\Payment\PaymentService;

class PosTerminal extends Component
{
    // Filtres produits
    public ?int   $categoryId = null;
    public string $search     = '';

    // Panier : [['product_id','product_name','unit_price','quantity','discount','total_price'], ...]
    public array $cart = [];

    // Modal paiement
    public bool   $showPayment    = false;
    public string $paymentMode    = 'cash';
    public float  $amountGiven    = 0;
    public float  $discountAmount = 0;
    public string $saleNotes      = '';
    public ?int   $customerId     = null;
    public string $paymentPhone   = '';

    // Paiement mobile (gateway : Wave / Orange / Moov)
    public bool    $showGateway     = false;
    public ?int    $txId            = null;
    public ?string $checkoutUrl     = null;
    public ?string $qrSvg           = null;
    public ?string $gatewayProvider = null;
    public string  $gatewayStatus   = 'pending';
    public ?string $gatewayError    = null;

    // Dernière vente confirmée
    public ?int  $lastSaleId  = null;
    public bool  $showReceipt = false;
    public array $receiptData = [];

    public function updatedSearch(): void    { /* reactive, no reset needed */ }
    public function updatedCategoryId(): void { $this->search = ''; }

    public function selectCustomer(?string $id): void
    {
        $this->customerId = $id ? (int) $id : null;
    }

    // ── Panier ───────────────────────────────────────────────────────────────

    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);
        if (! $product) return;

        foreach ($this->cart as $i => $item) {
            if ($item['product_id'] === $productId) {
                $this->cart[$i]['quantity'] += 1;
                $this->cart[$i]['total_price'] = round(
                    ($this->cart[$i]['unit_price'] * $this->cart[$i]['quantity']) - $this->cart[$i]['discount'], 2
                );
                return;
            }
        }

        $this->cart[] = [
            'product_id'   => $productId,
            'product_name' => $product->name,
            'unit_price'   => (float) $product->selling_price,
            'quantity'     => 1,
            'discount'     => 0,
            'total_price'  => (float) $product->selling_price,
        ];
    }

    public function updateQty(int $index, mixed $qty): void
    {
        $qty = (float) $qty;
        if ($qty <= 0) {
            $this->removeFromCart($index);
            return;
        }
        $this->cart[$index]['quantity']    = $qty;
        $this->cart[$index]['total_price'] = round(
            ($this->cart[$index]['unit_price'] * $qty) - $this->cart[$index]['discount'], 2
        );
    }

    public function removeFromCart(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function clearCart(): void
    {
        $this->cart           = [];
        $this->discountAmount = 0;
        $this->saleNotes      = '';
        $this->customerId     = null;
    }

    public function closeReceipt(): void
    {
        $this->showReceipt = false;
        $this->receiptData = [];
        $this->lastSaleId  = null;
    }

    // ── Totaux ───────────────────────────────────────────────────────────────

    public function getSubtotalProperty(): float
    {
        return round(collect($this->cart)->sum('total_price'), 2);
    }

    public function getTotalProperty(): float
    {
        return max(0, round($this->subtotal - $this->discountAmount, 2));
    }

    public function getChangeProperty(): float
    {
        return max(0, round($this->amountGiven - $this->total, 2));
    }

    // ── Paiement ─────────────────────────────────────────────────────────────

    public function openPayment(): void
    {
        if (empty($this->cart)) return;
        $this->amountGiven = $this->total;
        $this->showPayment = true;
    }

    public function confirmPayment(SaleService $service, CashSessionService $sessions, PaymentService $payments): void
    {
        $this->authorize('create-sales');

        if (empty($this->cart)) {
            $this->addError('cart', 'Le panier est vide.');
            return;
        }

        $rules = [
            'paymentMode'   => 'required|in:cash,card,mobile_money,orange_money,moov_money,wave,credit',
            'amountGiven'   => 'required|numeric|min:0',
            'discountAmount'=> 'nullable|numeric|min:0',
        ];
        if ($this->paymentMode === 'credit') {
            $rules['customerId'] = 'required|integer|exists:customers,id';
        }
        $this->validate($rules);

        $mode = PaymentMode::from($this->paymentMode);

        if ($mode->isCash() && $this->amountGiven < $this->total) {
            $this->addError('amountGiven', 'Montant insuffisant.');
            return;
        }

        if ($service->discountExceedsLimit($this->discountAmount, $this->subtotal)) {
            $this->addError('discountAmount', 'Remise dépasse le plafond autorisé par les paramètres.');
            return;
        }

        // ── Paiement mobile (asynchrone) : on initie le paiement AVANT la vente ──
        if ($mode->isGateway()) {
            if ($mode === PaymentMode::MoovMoney && ! trim($this->paymentPhone)) {
                $this->addError('cart', 'Numéro de téléphone du client requis pour Moov Money.');
                return;
            }

            $reference = 'POS-' . now()->format('ymdHis') . '-' . Str::upper(Str::random(4));

            try {
                $tx = $payments->start($this->total, $reference, $mode->provider(), $this->paymentPhone ?: null);
            } catch (\RuntimeException $e) {
                $this->addError('cart', $e->getMessage());
                return;
            }

            $this->txId            = $tx->id;
            $this->checkoutUrl     = $tx->checkout_url;
            $this->qrSvg           = $tx->checkout_url
                ? \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(176)->margin(0)->errorCorrection('M')->generate($tx->checkout_url)
                : null;
            $this->gatewayProvider = $mode->provider();
            $this->gatewayStatus   = $tx->status;
            $this->gatewayError    = null;
            $this->showPayment     = false;
            $this->showGateway     = true;
            return;
        }

        // ── Paiement immédiat (espèces / carte / crédit) ──
        $session       = $sessions->currentSession();
        $paymentStatus = $this->paymentMode === 'credit' ? 'pending' : 'paid';

        try {
            $sale = $this->persistSale($service, $sessions, $paymentStatus);
        } catch (\RuntimeException $e) {
            $this->addError('customerId', $e->getMessage());
            return;
        }

        $this->finishWithReceipt($sale);
    }

    /**
     * Polling du statut du paiement mobile. Au succès, on crée la vente.
     */
    public function pollPayment(SaleService $service, CashSessionService $sessions, PaymentService $payments): void
    {
        if (! $this->showGateway || ! $this->txId) {
            return;
        }

        $tx = PaymentTransaction::find($this->txId);
        if (! $tx) {
            return;
        }

        $tx = $payments->poll($tx);
        $this->gatewayStatus = $tx->status;

        if ($tx->isSucceeded()) {
            try {
                $sale = $this->persistSale($service, $sessions, 'paid');
            } catch (\RuntimeException $e) {
                $this->gatewayError = $e->getMessage();
                return;
            }

            $payments->attachSale($tx, $sale);
            $this->showGateway = false;
            $this->finishWithReceipt($sale);
            return;
        }

        if (in_array($tx->status, ['failed', 'cancelled', 'expired'], true)) {
            $this->gatewayError = 'Paiement ' . match($tx->status) {
                'cancelled' => 'annulé par le client.',
                'expired'   => 'expiré.',
                default     => 'échoué.',
            };
        }
    }

    public function cancelGatewayPayment(): void
    {
        if ($this->txId && $tx = PaymentTransaction::find($this->txId)) {
            if (! $tx->isFinished()) {
                $tx->update(['status' => PaymentTransaction::CANCELLED]);
            }
        }
        $this->resetGateway();
    }

    private function resetGateway(): void
    {
        $this->showGateway     = false;
        $this->txId            = null;
        $this->checkoutUrl     = null;
        $this->qrSvg           = null;
        $this->gatewayProvider = null;
        $this->gatewayStatus   = 'pending';
        $this->gatewayError    = null;
    }

    private function persistSale(SaleService $service, CashSessionService $sessions, string $paymentStatus)
    {
        $session = $sessions->currentSession();

        return $service->createFromCart(
            cartItems: $this->cart,
            payment: [
                'mode'           => $this->paymentMode,
                'payment_status' => $paymentStatus,
                'discount'       => $this->discountAmount,
                'notes'          => $this->saleNotes,
                'customer_id'    => $this->customerId,
            ],
            sessionId: $session?->id,
        );
    }

    private function finishWithReceipt($sale): void
    {
        $this->receiptData = [
            'id'            => $sale->id,
            'number'        => $sale->number,
            'created_at'    => $sale->created_at->format('d/m/Y H:i'),
            'items'         => $this->cart,
            'subtotal'      => $this->subtotal,
            'discount'      => $this->discountAmount,
            'total'         => $this->total,
            'payment_mode'  => $this->paymentMode,
            'payment_label' => PaymentMode::from($this->paymentMode)->label(),
            'amount_given'  => $this->amountGiven,
            'change'        => $this->change,
            'notes'         => $this->saleNotes,
            'served_by'     => auth()->user()?->name ?? '',
        ];

        $saleId            = $sale->id;
        $this->resetGateway();
        $this->showPayment = false;
        $this->clearCart();
        $this->lastSaleId  = $saleId;
        $this->showReceipt = true;

        $this->dispatch('sale-completed', saleId: $saleId);
    }

    // ── Rendu ────────────────────────────────────────────────────────────────

    public function render(CashSessionService $sessions)
    {
        $currentSession = $sessions->currentSession();
        $currentSession?->loadMissing(['cashRegister', 'openedBy']);

        $categories = Category::active()->ordered()->get();

        $products = Product::active()
            ->select(['id', 'name', 'selling_price', 'image', 'category_id', 'stock_quantity', 'min_stock'])
            ->with('category:id,name,color')
            ->when($this->categoryId, fn($q) => $q->where('category_id', $this->categoryId))
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();

        $paymentModes = PaymentMode::options();
        $customers    = Customer::active()->orderBy('name')->get();

        $subtotal = $this->getSubtotalProperty();
        $total    = $this->getTotalProperty();
        $change   = $this->getChangeProperty();

        return view('sales::livewire.pos-terminal',
            compact('currentSession', 'categories', 'products', 'paymentModes', 'customers',
                    'subtotal', 'total', 'change'));
    }
}
