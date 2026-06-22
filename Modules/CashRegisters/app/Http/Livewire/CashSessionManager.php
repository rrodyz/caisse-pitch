<?php

namespace Modules\CashRegisters\app\Http\Livewire;

use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\CashRegisters\app\Models\CashRegister;
use Modules\CashRegisters\app\Models\CashSession;
use Modules\CashRegisters\app\Services\CashSessionService;

class CashSessionManager extends Component
{
    use WithPagination;

    public string $view = 'list';

    // Ouverture
    public ?int   $openRegisterId  = null;
    public float  $openingAmount   = 0;
    public string $openingNotes    = '';

    // Clôture
    #[Locked]
    public ?int   $closingSessionId = null;
    public float  $closingAmount    = 0;
    public string $closingNotes     = '';

    // Filtres liste
    public string $filterStatus = '';
    public string $dateFrom     = '';
    public string $dateTo       = '';

    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedDateFrom(): void     { $this->resetPage(); }
    public function updatedDateTo(): void       { $this->resetPage(); }

    // ── Ouverture ────────────────────────────────────────────────────────────

    public function showOpenForm(): void
    {
        $this->authorize('open-cash-session');
        $this->reset(['openRegisterId', 'openingNotes']);
        $this->openingAmount = 0;
        $this->view = 'open';
    }

    public function openSession(CashSessionService $service): void
    {
        $this->authorize('open-cash-session');
        $this->validate([
            'openRegisterId' => 'required|integer|exists:cash_registers,id',
            'openingAmount'  => 'required|numeric|min:0',
        ]);

        try {
            $service->open($this->openRegisterId, $this->openingAmount, $this->openingNotes);
            session()->flash('success', 'Session ouverte.');
            $this->view = 'list';
            $this->resetPage();
        } catch (\RuntimeException $e) {
            $this->addError('openRegisterId', $e->getMessage());
        }
    }

    // ── Clôture ──────────────────────────────────────────────────────────────

    public function showCloseForm(int $sessionId): void
    {
        $this->authorize('close-cash-session');
        $session = CashSession::findOrFail($sessionId);

        if (! $session->isOpen()) {
            session()->flash('error', 'Session déjà clôturée.');
            return;
        }

        $this->closingSessionId = $sessionId;
        $this->closingAmount    = 0;
        $this->closingNotes     = '';
        $this->view = 'close';
    }

    public function closeSession(CashSessionService $service): void
    {
        $this->authorize('close-cash-session');
        $this->validate([
            'closingSessionId' => 'required|integer|exists:cash_sessions,id',
            'closingAmount'    => 'required|numeric|min:0',
        ]);

        try {
            $session = CashSession::findOrFail($this->closingSessionId);
            $service->close($session, $this->closingAmount, $this->closingNotes);
            session()->flash('success', 'Session clôturée.');
            $this->view = 'list';
            $this->resetPage();
        } catch (\RuntimeException $e) {
            $this->addError('closingAmount', $e->getMessage());
        }
    }

    public function cancelForm(): void
    {
        $this->view = 'list';
    }

    // ── Rendu ────────────────────────────────────────────────────────────────

    public function render()
    {
        if ($this->view === 'open') {
            $availableRegisters = CashRegister::active()
                ->with('activeSession')
                ->orderBy('name')
                ->get();
            return view('cashregisters::livewire.cash-session-open',
                compact('availableRegisters'));
        }

        if ($this->view === 'close') {
            $session = CashSession::with('cashRegister', 'openedBy')->findOrFail($this->closingSessionId);
            return view('cashregisters::livewire.cash-session-close', compact('session'));
        }

        // list
        $sessions = CashSession::with('cashRegister', 'openedBy', 'closedBy')
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->dateFrom,     fn($q) => $q->whereDate('opened_at', '>=', $this->dateFrom))
            ->when($this->dateTo,       fn($q) => $q->whereDate('opened_at', '<=', $this->dateTo))
            ->orderByDesc('opened_at')
            ->paginate(25);

        $currentSession = app(CashSessionService::class)->currentSession();

        return view('cashregisters::livewire.cash-session-list',
            compact('sessions', 'currentSession'));
    }
}
