<?php

namespace Modules\Reports\app\Http\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PaymentReport extends Component
{
    public string $period   = 'month';
    public string $dateFrom = '';
    public string $dateTo   = '';
    public string $view     = 'modes'; // modes | daily

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function updatedPeriod(): void
    {
        match ($this->period) {
            'today' => [$this->dateFrom, $this->dateTo] = [now()->format('Y-m-d'), now()->format('Y-m-d')],
            'week'  => [$this->dateFrom, $this->dateTo] = [now()->startOfWeek()->format('Y-m-d'), now()->endOfWeek()->format('Y-m-d')],
            'month' => [$this->dateFrom, $this->dateTo] = [now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d')],
            'year'  => [$this->dateFrom, $this->dateTo] = [now()->startOfYear()->format('Y-m-d'), now()->endOfYear()->format('Y-m-d')],
            default => null,
        };
    }

    public function render()
    {
        $this->authorize('view-reports');

        $byMode = $this->buildByMode();
        $daily  = $this->view === 'daily' ? $this->buildDaily() : collect();
        $totals = $this->buildTotals();

        return view('reports::livewire.payment-report', compact('byMode', 'daily', 'totals'));
    }

    private function buildByMode(): \Illuminate\Support\Collection
    {
        return DB::table('sales')
            ->where('status', 'completed')
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->selectRaw('payment_mode, COUNT(*) as cnt, SUM(total_amount) as total, AVG(total_amount) as avg_ticket')
            ->groupBy('payment_mode')
            ->orderByDesc('total')
            ->get()
            ->map(function ($r) {
                $r->label      = $this->modeLabel($r->payment_mode);
                $r->badge_class = $this->modeBadgeClass($r->payment_mode);
                return $r;
            });
    }

    private function buildDaily(): \Illuminate\Support\Collection
    {
        return DB::table('sales')
            ->where('status', 'completed')
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->selectRaw('DATE(created_at) as date, payment_mode, COUNT(*) as cnt, SUM(total_amount) as total')
            ->groupByRaw('DATE(created_at), payment_mode')
            ->orderByRaw('DATE(created_at) DESC, total DESC')
            ->get()
            ->map(function ($r) {
                $r->label      = $this->modeLabel($r->payment_mode);
                $r->badge_class = $this->modeBadgeClass($r->payment_mode);
                return $r;
            });
    }

    private function buildTotals(): array
    {
        $r = DB::table('sales')
            ->where('status', 'completed')
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->selectRaw('COUNT(*) as cnt, SUM(total_amount) as total')
            ->first();

        return ['cnt' => $r->cnt ?? 0, 'total' => $r->total ?? 0];
    }

    private function modeLabel(string $mode): string
    {
        return match($mode) {
            'cash'         => 'Espèces',
            'card'         => 'Carte bancaire',
            'mobile_money' => 'Mobile Money',
            'orange_money' => 'Orange Money',
            'moov_money'   => 'Moov Money',
            'wave'         => 'Wave',
            'credit'       => 'Crédit client',
            default        => $mode,
        };
    }

    private function modeBadgeClass(string $mode): string
    {
        return match($mode) {
            'cash'         => 'bg-emerald-500/15 text-emerald-300',
            'card'         => 'bg-blue-500/15 text-blue-300',
            'mobile_money' => 'bg-amber-500/15 text-amber-300',
            'orange_money' => 'bg-orange-500/15 text-orange-300',
            'moov_money'   => 'bg-cyan-500/15 text-cyan-300',
            'wave'         => 'bg-violet-500/15 text-violet-300',
            'credit'       => 'bg-red-500/15 text-red-300',
            default        => 'bg-night-600 text-night-300',
        };
    }
}
