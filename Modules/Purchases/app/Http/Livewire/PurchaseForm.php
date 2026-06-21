<?php

namespace Modules\Purchases\app\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Products\app\Models\Product;
use Modules\Purchases\app\Models\Purchase;
use Modules\Purchases\app\Models\PurchaseItem;
use Modules\Suppliers\app\Models\Supplier;

class PurchaseForm extends Component
{
    use WithFileUploads;

    public ?int $purchaseId = null;

    public string $number       = '';
    public string $date         = '';
    public ?int   $supplier_id  = null;
    public string $payment_mode = '';
    public string $payment_status = 'pending';
    public float  $fees         = 0;
    public string $notes        = '';
    public        $receipt      = null;

    public array $items = [];

    // Totaux calculés
    public float $subtotal     = 0;
    public float $total_amount = 0;

    public function mount(?int $purchaseId = null): void
    {
        $this->purchaseId = $purchaseId;
        $this->date       = now()->toDateString();
        $this->number     = Purchase::generateNumber();

        if ($purchaseId) {
            $purchase = Purchase::with('items.product')->findOrFail($purchaseId);

            if ($purchase->isValidated() || $purchase->isCancelled()) {
                session()->flash('error', 'Cet achat ne peut plus être modifié.');
                $this->redirect(route('purchases.index'));
                return;
            }

            $this->number         = $purchase->number;
            $this->date           = $purchase->date->toDateString();
            $this->supplier_id    = $purchase->supplier_id;
            $this->payment_mode   = $purchase->payment_mode ?? '';
            $this->payment_status = $purchase->payment_status;
            $this->fees           = $purchase->fees;
            $this->notes          = $purchase->notes ?? '';

            $this->items = $purchase->items->map(fn($item) => [
                'product_id'   => $item->product_id,
                'product_name' => $item->product_name,
                'quantity'     => $item->quantity,
                'unit_price'   => $item->unit_price,
                'total'        => $item->total_price,
            ])->toArray();
        }

        if (empty($this->items)) {
            $this->addItem();
        }

        $this->computeTotals();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id'   => null,
            'product_name' => '',
            'quantity'     => 1,
            'unit_price'   => 0,
            'total'        => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->computeTotals();
    }

    public function updatedItems($value, $key): void
    {
        // key format: "0.quantity" or "0.unit_price" or "0.product_id"
        [$index, $field] = explode('.', $key);
        $index = (int) $index;

        // Auto-fill product name + unit_price quand product_id change
        if ($field === 'product_id' && $value) {
            $product = Product::find($value);
            if ($product) {
                $this->items[$index]['product_name'] = $product->name;
                $this->items[$index]['unit_price']   = $product->purchase_price;
            }
        }

        // Recalcul total ligne
        $qty   = (float) ($this->items[$index]['quantity']  ?? 0);
        $price = (float) ($this->items[$index]['unit_price'] ?? 0);
        $this->items[$index]['total'] = round($qty * $price, 2);

        $this->computeTotals();
    }

    public function updatedFees(): void
    {
        $this->computeTotals();
    }

    private function computeTotals(): void
    {
        $this->subtotal     = collect($this->items)->sum('total');
        $this->total_amount = $this->subtotal + (float) $this->fees;
    }

    public function save(bool $validate = false): void
    {
        $this->authorize('create-purchases');

        $this->validate([
            'date'           => 'required|date',
            'supplier_id'    => 'nullable|exists:suppliers,id',
            'payment_mode'   => 'nullable|in:espèces,virement,chèque,mobile_money',
            'payment_status' => 'required|in:pending,partial,paid',
            'fees'           => 'numeric|min:0',
            'items'          => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.quantity'     => 'required|numeric|min:0.001',
            'items.*.unit_price'   => 'required|numeric|min:0',
        ]);

        $this->computeTotals();

        $data = [
            'number'         => $this->number,
            'date'           => $this->date,
            'supplier_id'    => $this->supplier_id ?: null,
            'payment_mode'   => $this->payment_mode ?: null,
            'payment_status' => $this->payment_status,
            'subtotal'       => $this->subtotal,
            'fees'           => $this->fees,
            'total_amount'   => $this->total_amount,
            'notes'          => $this->notes ?: null,
            'created_by'     => auth()->id(),
        ];

        if ($this->receipt) {
            $this->validate(['receipt' => 'file|mimes:pdf,jpg,jpeg,png|max:5120']);
            $data['receipt_path'] = $this->receipt->store('receipts', 'private');
        }

        if ($this->purchaseId) {
            $purchase = Purchase::findOrFail($this->purchaseId);
            $purchase->update($data);
            $purchase->items()->delete();
        } else {
            $purchase = Purchase::create($data);
        }

        foreach ($this->items as $item) {
            PurchaseItem::create([
                'purchase_id'  => $purchase->id,
                'product_id'   => $item['product_id'],
                'product_name' => $item['product_name'] ?: Product::find($item['product_id'])?->name ?? '',
                'quantity'     => $item['quantity'],
                'unit_price'   => $item['unit_price'],
                'total_price'  => $item['total'],
            ]);
        }

        if ($validate) {
            $this->authorize('validate-purchases');
            $purchase->validate(auth()->id());
            session()->flash('success', "Achat {$purchase->number} enregistré et validé.");
        } else {
            session()->flash('success', "Achat {$purchase->number} enregistré en brouillon.");
        }

        $this->redirect(route('purchases.index'));
    }

    public function saveAndValidate(): void
    {
        $this->save(validate: true);
    }

    public function render()
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products  = Product::active()->with('category')->orderBy('name')->get();

        return view('purchases::livewire.purchase-form', compact('suppliers', 'products'));
    }
}
