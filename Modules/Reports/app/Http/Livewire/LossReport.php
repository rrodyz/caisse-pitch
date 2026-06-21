<?php

namespace Modules\Reports\app\Http\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class LossReport extends Component
{
    use WithPagination;

    public string $period   = 'month';
    public string $dateFrom = '';
    public string $dateTo   = '';
    public string $lossType = '';
    public string $search   = '';

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
        $this->resetPage();
    }

    public function updatedLossType(): void { $this->resetPage(); }
    public function updatedSearch(): void   { $this->resetPage(); }

    public function render()
    {
        $this->authorize('view-reports');

        $query = DB::table('losses as l')
            ->join('products as p', 'p.id', '=', 'l.product_id')
            ->leftJoin('users as u', 'u.id', '=', 'l.declared_by')
            ->when($this->dateFrom, fn($q) => $q->whereDate('l.created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('l.created_at', '<=', $this->dateTo))
            ->when($this->lossType, fn($q) => $q->where('l.type', $this->lossType))
            ->when($this->search,   fn($q) => $q->where('p.name', 'like', "%{$this->search}%"))
            ->selectRaw('l.id, l.type, l.quantity, l.unit_cost, l.total_cost, l.reason,
                l.created_at, p.name as product_name, p.unit as product_unit,
                ' . $this->nameExpr() . ' as user_name')
            ->orderByDesc('l.created_at');

        $rows = $query->paginate(25);

        $summary = $this->buildSummary();

        return view('reports::livewire.loss-report', compact('rows', 'summary'));
    }

    public function buildSummary(): array
    {
        $base = DB::table('losses')
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('created_at', '<=', $this->dateTo));

        $total = (clone $base)->selectRaw('COUNT(*) as cnt, SUM(total_cost) as cost, SUM(quantity) as qty')->first();

        $byType = [];
        foreach (['loss', 'break', 'gift'] as $t) {
            $r = (clone $base)->where('type', $t)
                ->selectRaw('COUNT(*) as cnt, SUM(total_cost) as cost, SUM(quantity) as qty')
                ->first();
            $byType[$t] = ['cnt' => $r->cnt ?? 0, 'cost' => $r->cost ?? 0, 'qty' => $r->qty ?? 0];
        }

        return [
            'count'   => $total->cnt  ?? 0,
            'cost'    => $total->cost ?? 0,
            'qty'     => $total->qty  ?? 0,
            'by_type' => $byType,
        ];
    }

    private function nameExpr(string $alias = 'u'): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "({$alias}.first_name || ' ' || {$alias}.last_name)"
            : "CONCAT({$alias}.first_name, ' ', {$alias}.last_name)";
    }
}
