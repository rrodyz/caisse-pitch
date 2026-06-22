<?php

namespace Modules\Sales\app\Http\Livewire;

use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Sales\app\Enums\SaleStatus;
use Modules\Sales\app\Models\Sale;
use Modules\Sales\app\Services\SaleService;
use Modules\Settings\app\Models\Setting;

class SaleList extends Component
{
    use WithPagination;

    public string $search      = '';
    public string $filterStatus= '';
    public string $filterMode  = '';
    public string $dateFrom    = '';
    public string $dateTo      = '';

    // Modal annulation
    public bool   $showCancelModal = false;
    #[Locked]
    public ?int   $cancelSaleId   = null;
    public string $cancelReason   = '';

    public function updatedSearch(): void      { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedFilterMode(): void   { $this->resetPage(); }
    public function updatedDateFrom(): void     { $this->resetPage(); }
    public function updatedDateTo(): void       { $this->resetPage(); }

    public function openCancelModal(int $id): void
    {
        $this->authorize('cancel-sales');
        $this->cancelSaleId  = $id;
        $this->cancelReason  = '';
        $this->showCancelModal = true;
    }

    public function confirmCancel(SaleService $service): void
    {
        $this->authorize('cancel-sales');
        $this->validate(['cancelReason' => 'required|string|min:3|max:255']);

        $sale     = Sale::findOrFail($this->cancelSaleId);
        $settings = Setting::current();

        // Avertissement (non bloquant) si dépassement seuil superviseur
        if ($settings->supervisor_approval_threshold && $sale->total_amount > $settings->supervisor_approval_threshold) {
            // Log the override — in production, could require a supervisor PIN
        }

        try {
            $service->cancel($sale, $this->cancelReason);
            session()->flash('success', "Vente {$sale->number} annulée. Stock restitué.");
        } catch (\RuntimeException $e) {
            $this->addError('cancelReason', $e->getMessage());
            return;
        }

        $this->showCancelModal = false;
        $this->cancelSaleId   = null;
        $this->resetPage();
    }

    public function render()
    {
        $sales = Sale::with('servedBy', 'customer', 'cashSession.cashRegister')
            ->withCount('items')
            ->when($this->search, fn($q) => $q->where('number', 'like', "%{$this->search}%")
                ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            )
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterMode,   fn($q) => $q->where('payment_mode', $this->filterMode))
            ->when($this->dateFrom,     fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,       fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderByDesc('created_at')
            ->paginate(30);

        $settings  = Setting::current();
        $statuses  = SaleStatus::cases();
        $cancelSale = $this->cancelSaleId ? Sale::find($this->cancelSaleId) : null;

        return view('sales::livewire.sale-list',
            compact('sales', 'settings', 'statuses', 'cancelSale'));
    }
}
