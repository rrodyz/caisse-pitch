<?php

namespace Modules\Sales\app\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Sales\app\Models\CreditPayment;
use Modules\Sales\app\Models\Customer;
use Modules\Sales\app\Services\SaleService;

class CustomerManager extends Component
{
    use WithPagination;

    public string $view        = 'list';
    public string $search      = '';
    public bool   $showInactive = false;

    // Formulaire client (array pour grouper)
    public bool  $showForm  = false;
    public ?int  $editingId = null;
    public array $form = [
        'name'         => '',
        'phone'        => '',
        'email'        => '',
        'credit_limit' => 0,
        'notes'        => '',
        'is_active'    => true,
    ];

    // Paiement crédit
    public bool   $showPaymentModal  = false;
    public ?int   $payingCustomerId  = null;
    public array  $paymentForm = [
        'amount'       => '',
        'payment_mode' => 'cash',
        'notes'        => '',
    ];

    // Détail client
    public ?int    $viewingCustomerId = null;
    public string  $activeTab        = 'sales';

    public function updatedSearch(): void        { $this->resetPage(); }
    public function updatedShowInactive(): void  { $this->resetPage(); }

    // ── CRUD Clients ─────────────────────────────────────────────────────────

    public function openCreateForm(): void
    {
        $this->authorize('create-customers');
        $this->editingId = null;
        $this->form = ['name'=>'','phone'=>'','email'=>'','credit_limit'=>0,'notes'=>'','is_active'=>true];
        $this->showForm = true;
    }

    public function openEditForm(int $id): void
    {
        $this->authorize('edit-customers');
        $c = Customer::findOrFail($id);
        $this->editingId = $id;
        $this->form = [
            'name'         => $c->name,
            'phone'        => $c->phone ?? '',
            'email'        => $c->email ?? '',
            'credit_limit' => $c->credit_limit,
            'notes'        => $c->notes ?? '',
            'is_active'    => $c->is_active,
        ];
        $this->showForm = true;
    }

    public function save(): void
    {
        $rules = [
            'form.name'         => 'required|string|max:150',
            'form.phone'        => 'nullable|string|max:20',
            'form.email'        => 'nullable|email|max:150',
            'form.credit_limit' => 'nullable|numeric|min:0',
        ];
        $this->validate($rules);

        $data = [
            'name'         => $this->form['name'],
            'phone'        => $this->form['phone'] ?: null,
            'email'        => $this->form['email'] ?: null,
            'credit_limit' => $this->form['credit_limit'],
            'notes'        => $this->form['notes'] ?: null,
            'is_active'    => $this->form['is_active'],
        ];

        if ($this->editingId) {
            $this->authorize('edit-customers');
            Customer::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Client mis à jour.');
        } else {
            $this->authorize('create-customers');
            Customer::create($data);
            session()->flash('success', 'Client créé.');
        }

        $this->showForm = false;
        $this->resetPage();
    }

    public function deleteCustomer(int $id): void
    {
        $this->authorize('delete-customers');
        $customer = Customer::findOrFail($id);

        if ($customer->current_credit > 0) {
            session()->flash('error', 'Impossible : ce client a un encours de crédit non soldé.');
            return;
        }

        $customer->delete();
        session()->flash('success', 'Client supprimé.');
        $this->resetPage();
    }

    // ── Paiement crédit ──────────────────────────────────────────────────────

    public function openPayment(int $customerId): void
    {
        $this->authorize('create-customers');
        $this->payingCustomerId = $customerId;
        $this->paymentForm = ['amount' => '', 'payment_mode' => 'cash', 'notes' => ''];
        $this->showPaymentModal = true;
    }

    public function confirmPayment(SaleService $service): void
    {
        $this->authorize('create-customers');
        $this->validate([
            'paymentForm.amount'       => 'required|numeric|min:1',
            'paymentForm.payment_mode' => 'required|in:cash,card,mobile_money',
        ]);

        $customer = Customer::findOrFail($this->payingCustomerId);

        try {
            $service->recordCreditPayment(
                $customer,
                (float) $this->paymentForm['amount'],
                $this->paymentForm['payment_mode'],
                $this->paymentForm['notes'] ?? '',
            );
            session()->flash('success', 'Encaissement de ' . number_format((float)$this->paymentForm['amount'], 0, ',', ' ') . ' FCFA enregistré.');
        } catch (\RuntimeException $e) {
            $this->addError('paymentForm.amount', $e->getMessage());
            return;
        }

        $this->showPaymentModal = false;
        $this->payingCustomerId = null;

        // Refresh detail if viewing this customer
        if ($this->viewingCustomerId === $customer->id) {
            $this->viewingCustomerId = $customer->id; // triggers re-render
        }
    }

    // ── Vue détail ───────────────────────────────────────────────────────────

    public function viewCustomer(int $id): void
    {
        $this->viewingCustomerId = $id;
        $this->activeTab         = 'sales';
        $this->view              = 'detail';
        $this->resetPage();
    }

    public function backToList(): void
    {
        $this->view              = 'list';
        $this->viewingCustomerId = null;
        $this->resetPage();
    }

    // ── Rendu ────────────────────────────────────────────────────────────────

    public function render()
    {
        if ($this->view === 'detail' && $this->viewingCustomerId) {
            $customer = Customer::findOrFail($this->viewingCustomerId);

            $sales = $customer->sales()
                ->withCount('items')
                ->orderByDesc('created_at')
                ->paginate(20);

            $payments = $customer->creditPayments()
                ->with(['receivedBy', 'sale'])
                ->orderByDesc('created_at')
                ->paginate(20);

            $salesTotal    = $customer->sales()->count();
            $paymentsTotal = $customer->creditPayments()->count();
            $payingCustomer = $this->payingCustomerId ? Customer::find($this->payingCustomerId) : null;

            return view('sales::livewire.customer-detail',
                compact('customer', 'sales', 'payments', 'salesTotal', 'paymentsTotal', 'payingCustomer'));
        }

        $customers = Customer::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
            )
            ->when(!$this->showInactive, fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->paginate(25);

        $payingCustomer = $this->payingCustomerId ? Customer::find($this->payingCustomerId) : null;

        return view('sales::livewire.customer-manager', compact('customers', 'payingCustomer'));
    }
}
