<?php

namespace Modules\Reports\app\Http\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Reports\app\Exports\SalesReportExport;

class SalesReport extends Component
{
    public string $period  = 'month'; // today | week | month | year | custom
    public string $dateFrom = '';
    public string $dateTo   = '';
    public string $groupBy  = 'day';  // day | product | category | payment_mode | transactions

    public function mount(): void
    {
        $this->applyPeriod();
    }

    public function updatedPeriod(): void
    {
        $this->applyPeriod();
    }

    private function applyPeriod(): void
    {
        $this->dateFrom = match($this->period) {
            'today' => now()->format('Y-m-d'),
            'week'  => now()->startOfWeek()->format('Y-m-d'),
            'month' => now()->startOfMonth()->format('Y-m-d'),
            'year'  => now()->startOfYear()->format('Y-m-d'),
            default => $this->dateFrom,
        };
        if ($this->period !== 'custom') {
            $this->dateTo = now()->format('Y-m-d');
        }
        // Auto-set groupBy to a sensible default per period
        if ($this->period === 'today' && $this->groupBy === 'day') {
            $this->groupBy = 'payment_mode';
        }
    }

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('export-reports');
        return Excel::download(
            new SalesReportExport($this->dateFrom, $this->dateTo, $this->groupBy),
            'rapport_ventes_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    private function buildRows(): \Illuminate\Support\Collection
    {
        $base = DB::table('sales as s')
            ->where('s.status', 'completed')
            ->when($this->dateFrom, fn($q) => $q->whereDate('s.created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('s.created_at', '<=', $this->dateTo));

        return match($this->groupBy) {
            'day' => $base
                ->selectRaw('DATE(s.created_at) as label,
                    COUNT(*) as count,
                    SUM(s.total_amount) as total,
                    SUM(s.discount_amount) as discounts,
                    AVG(s.total_amount) as avg_ticket')
                ->groupByRaw('DATE(s.created_at)')
                ->orderByRaw('DATE(s.created_at)')
                ->get(),

            'payment_mode' => $base
                ->selectRaw('s.payment_mode as label,
                    COUNT(*) as count,
                    SUM(s.total_amount) as total,
                    SUM(s.discount_amount) as discounts,
                    AVG(s.total_amount) as avg_ticket')
                ->groupBy('s.payment_mode')
                ->orderByDesc('total')
                ->get()
                ->map(function ($r) {
                    $r->label = match($r->label) {
                        'cash'         => 'Espèces',
                        'card'         => 'Carte bancaire',
                        'mobile_money' => 'Mobile Money',
                        'orange_money' => 'Orange Money',
                        'moov_money'   => 'Moov Money',
                        'wave'         => 'Wave',
                        'credit'       => 'Crédit client',
                        default        => $r->label,
                    };
                    return $r;
                }),

            'category' => $base
                ->join('sale_items as si', 'si.sale_id', '=', 's.id')
                ->join('products as p', 'p.id', '=', 'si.product_id')
                ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
                ->selectRaw('COALESCE(c.name, "Sans catégorie") as label,
                    COUNT(DISTINCT s.id) as count,
                    SUM(si.total_price) as total,
                    0 as discounts,
                    SUM(si.quantity) as qty')
                ->groupBy('c.id', 'c.name')
                ->orderByDesc('total')
                ->get(),

            'product' => $base
                ->join('sale_items as si', 'si.sale_id', '=', 's.id')
                ->join('products as p', 'p.id', '=', 'si.product_id')
                ->selectRaw('p.name as label,
                    COUNT(DISTINCT s.id) as count,
                    SUM(si.quantity) as qty,
                    SUM(si.total_price) as total,
                    AVG(si.unit_price) as avg_price')
                ->groupBy('p.id', 'p.name')
                ->orderByDesc('total')
                ->get(),

            'transactions' => $base
                ->join('sale_items as si', 'si.sale_id', '=', 's.id')
                ->join('products as p', 'p.id', '=', 'si.product_id')
                ->selectRaw("s.id,
                    s.number as label,
                    DATE_FORMAT(s.created_at, '%d/%m %H:%i') as datetime,
                    s.payment_mode,
                    SUM(si.quantity) as qty,
                    s.total_amount as total,
                    GROUP_CONCAT(
                        CONCAT(
                            IF(si.quantity = FLOOR(si.quantity),
                               CAST(FLOOR(si.quantity) AS CHAR),
                               FORMAT(si.quantity, 1)
                            ),
                            'x ', p.name)
                        ORDER BY p.name SEPARATOR ' · '
                    ) as products_detail")
                ->groupBy('s.id', 's.number', 's.created_at', 's.total_amount', 's.payment_mode')
                ->orderByDesc('s.created_at')
                ->get(),

            default => collect(),
        };
    }

    private function buildSummary(): array
    {
        $base = DB::table('sales')
            ->where('status', 'completed')
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('created_at', '<=', $this->dateTo));

        $completedTotal = (clone $base)->selectRaw(
            'COUNT(*) as cnt, SUM(total_amount) as total, SUM(discount_amount) as discounts, AVG(total_amount) as avg'
        )->first();

        $cancelledCount = DB::table('sales')
            ->where('status', 'cancelled')
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->count();

        return [
            'count'          => $completedTotal->cnt ?? 0,
            'total'          => $completedTotal->total ?? 0,
            'discounts'      => $completedTotal->discounts ?? 0,
            'avg_ticket'     => $completedTotal->avg ?? 0,
            'cancelled_count'=> $cancelledCount,
        ];
    }

    public function render()
    {
        $this->authorize('view-reports');
        $rows    = $this->buildRows();
        $summary = $this->buildSummary();
        $grandTotal = $rows->sum('total') ?: 1;

        return view('reports::livewire.sales-report', compact('rows', 'summary', 'grandTotal'));
    }
}
