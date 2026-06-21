<?php

namespace Modules\Sales\app\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Recipes\app\Services\RecipeService;
use Modules\Sales\app\Enums\PaymentMode;
use Modules\Sales\app\Enums\SaleStatus;
use Modules\Sales\app\Models\CreditPayment;
use Modules\Sales\app\Models\Customer;
use Modules\Sales\app\Models\Sale;
use Modules\Sales\app\Models\SaleItem;
use Modules\Settings\app\Models\Setting;
use Modules\Stock\app\Enums\MovementType;
use Modules\Stock\app\Services\StockService;
use RuntimeException;

class SaleService
{
    public function __construct(
        private RecipeService $recipes,
        private StockService  $stock,
    ) {}

    /**
     * Crée une vente complète depuis le panier POS.
     *
     * @param array    $cartItems  [['product_id','product_name','unit_price','quantity','discount','total_price'], ...]
     * @param array    $payment    ['mode','payment_status','discount','notes','customer_id']
     * @param int|null $sessionId
     */
    public function createFromCart(array $cartItems, array $payment, ?int $sessionId): Sale
    {
        return DB::transaction(function () use ($cartItems, $payment, $sessionId) {
            $subtotal   = collect($cartItems)->sum('total_price');
            $discount   = (float) ($payment['discount'] ?? 0);
            $total      = max(0, $subtotal - $discount);
            $customerId = isset($payment['customer_id']) && $payment['customer_id'] ? (int) $payment['customer_id'] : null;
            $mode       = PaymentMode::from($payment['mode']);

            // Vérification crédit client
            if ($mode === PaymentMode::Credit) {
                if (! $customerId) {
                    throw new RuntimeException('Un client est requis pour un paiement à crédit.');
                }
                $customer = Customer::lockForUpdate()->findOrFail($customerId);
                if ($customer->credit_limit > 0 && ($customer->current_credit + $total) > $customer->credit_limit) {
                    throw new RuntimeException(
                        "Plafond de crédit dépassé. Disponible : " . number_format($customer->availableCredit(), 0, ',', ' ') . " FCFA."
                    );
                }
            }

            $sale = Sale::create([
                'number'          => Sale::generateNumber(),
                'cash_session_id' => $sessionId,
                'customer_id'     => $customerId,
                'served_by'       => Auth::id(),
                'status'          => SaleStatus::Completed,
                'payment_mode'    => $mode,
                'payment_status'  => $payment['payment_status'] ?? 'paid',
                'subtotal'        => round($subtotal, 2),
                'discount_amount' => round($discount, 2),
                'total_amount'    => round($total, 2),
                'notes'           => $payment['notes'] ?? null,
            ]);

            foreach ($cartItems as $item) {
                SaleItem::create([
                    'sale_id'      => $sale->id,
                    'product_id'   => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'unit_price'   => $item['unit_price'],
                    'quantity'     => $item['quantity'],
                    'discount'     => $item['discount'] ?? 0,
                    'total_price'  => $item['total_price'],
                ]);

                $deductions = $this->recipes->getStockDeductions(
                    $item['product_id'],
                    (float) $item['quantity']
                );

                foreach ($deductions as $d) {
                    $this->stock->deductStock(
                        productId: $d['product_id'],
                        quantity:  $d['quantity'],
                        type:      MovementType::SaleOut,
                        notes:     "Vente {$sale->number}",
                        reference: $sale,
                    );
                }
            }

            // Incrémenter la dette client si crédit
            if ($mode === PaymentMode::Credit && $customerId) {
                Customer::where('id', $customerId)->increment('current_credit', $total);
            }

            return $sale->load('items');
        });
    }

    /**
     * Annule une vente et restitue le stock (+ rembourse le crédit si nécessaire).
     */
    public function cancel(Sale $sale, string $reason): Sale
    {
        if ($sale->status !== SaleStatus::Completed) {
            throw new RuntimeException('Seules les ventes complétées peuvent être annulées.');
        }

        return DB::transaction(function () use ($sale, $reason) {
            $sale->loadMissing('items');

            foreach ($sale->items as $item) {
                $deductions = $this->recipes->getStockDeductions(
                    $item->product_id,
                    (float) $item->quantity
                );
                foreach ($deductions as $d) {
                    $this->stock->addStock(
                        productId: $d['product_id'],
                        quantity:  $d['quantity'],
                        type:      MovementType::ManualIn,
                        notes:     "Annulation vente {$sale->number}",
                        reference: $sale,
                    );
                }
            }

            // Réduire la dette client si c'était un crédit
            if ($sale->payment_mode === PaymentMode::Credit && $sale->customer_id) {
                Customer::where('id', $sale->customer_id)
                    ->decrement('current_credit', $sale->total_amount);
            }

            $sale->update([
                'status'       => SaleStatus::Cancelled,
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now(),
                'cancel_reason'=> $reason,
            ]);

            return $sale->fresh();
        });
    }

    /**
     * Encaissement d'un remboursement de crédit client.
     */
    public function recordCreditPayment(Customer $customer, float $amount, string $mode, string $notes = '', ?int $saleId = null): CreditPayment
    {
        return DB::transaction(function () use ($customer, $amount, $mode, $notes, $saleId) {
            $amount = min($amount, $customer->current_credit);
            if ($amount <= 0) {
                throw new RuntimeException('Montant invalide ou client sans crédit.');
            }

            $payment = CreditPayment::create([
                'customer_id' => $customer->id,
                'sale_id'     => $saleId,
                'amount'      => $amount,
                'payment_mode'=> $mode,
                'notes'       => $notes ?: null,
                'received_by' => Auth::id(),
            ]);

            $customer->decrement('current_credit', $amount);

            return $payment;
        });
    }

    /**
     * Vérifie si la remise dépasse le plafond autorisé par les paramètres.
     */
    public function discountExceedsLimit(float $discountAmount, float $subtotal): bool
    {
        $settings = Setting::current();
        if (! $settings->max_discount_percent) return false;
        if ($subtotal <= 0) return false;
        return (($discountAmount / $subtotal) * 100) > $settings->max_discount_percent;
    }
}
