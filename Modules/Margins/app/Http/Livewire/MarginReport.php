<?php

namespace Modules\Margins\app\Http\Livewire;

use App\Support\RateLimitGuard;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Modules\Margins\app\Exports\MarginsExport;
use Maatwebsite\Excel\Facades\Excel;

class MarginReport extends Component
{
    public string $dateFrom   = '';
    public string $dateTo     = '';
    public string $groupBy    = 'product'; // product | category
    public string $sortBy     = 'revenue'; // revenue | margin | qty
    public ?int   $categoryId = null;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function export(): ?\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('view-reports');

        if (! RateLimitGuard::enforce('exports', fn (int $seconds) => $this->addError(
            'export',
            "Limite d'exports atteinte. Réessayez dans {$seconds} secondes."
        ))) {
            return null;
        }

        return Excel::download(
            new MarginsExport($this->dateFrom, $this->dateTo, $this->groupBy, $this->categoryId),
            'marges_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    private function buildQuery(): \Illuminate\Support\Collection
    {
        $orderCol = match($this->sortBy) {
            'margin' => 'margin_rate',
            'qty'    => 'total_qty',
            default  => 'total_revenue',
        };

        $base = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->join('products as p', 'p.id', '=', 'si.product_id')
            ->leftJoin('recipes as r', 'r.product_id', '=', 'p.id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->where('s.status', 'completed')
            ->whereNotNull('si.product_id')
            ->when($this->dateFrom, fn($q) => $q->whereDate('s.created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('s.created_at', '<=', $this->dateTo))
            ->when($this->categoryId, fn($q) => $q->where('p.category_id', $this->categoryId));

        if ($this->groupBy === 'category') {
            $rows = $base->selectRaw('
                    COALESCE(c.id, 0) as category_id,
                    COALESCE(c.name, "Sans catégorie") as label,
                    COUNT(DISTINCT p.id) as product_count,
                    SUM(si.quantity) as total_qty,
                    SUM(si.total_price) as total_revenue,
                    SUM(si.quantity * COALESCE(r.cost_price, p.purchase_price, 0)) as total_cost,
                    SUM(si.discount) as total_discount
                ')
                ->groupBy('c.id', 'c.name')
                ->orderByDesc('total_revenue')
                ->get();
        } else {
            $rows = $base->selectRaw('
                    p.id as product_id,
                    p.name as label,
                    COALESCE(c.name, "Sans catégorie") as category_name,
                    SUM(si.quantity) as total_qty,
                    SUM(si.total_price) as total_revenue,
                    SUM(si.quantity * COALESCE(r.cost_price, p.purchase_price, 0)) as total_cost,
                    SUM(si.discount) as total_discount
                ')
                ->groupBy('p.id', 'p.name', 'c.name')
                ->orderByDesc('total_revenue')
                ->get();
        }

        return $rows->map(function ($row) {
            $row->gross_margin = $row->total_revenue - $row->total_cost;
            $row->margin_rate  = $row->total_revenue > 0
                ? round($row->gross_margin / $row->total_revenue * 100, 1)
                : 0;
            $row->markup_rate  = $row->total_cost > 0
                ? round($row->gross_margin / $row->total_cost * 100, 1)
                : null;
            return $row;
        })->sortByDesc($orderCol)->values();
    }

    public function render()
    {
        $this->authorize('view-reports');

        $rows = $this->buildQuery();

        $summary = [
            'total_revenue' => $rows->sum('total_revenue'),
            'total_cost'    => $rows->sum('total_cost'),
            'total_margin'  => $rows->sum('gross_margin'),
            'avg_margin'    => $rows->avg('margin_rate'),
            'total_qty'     => $rows->sum('total_qty'),
        ];

        $categories = DB::table('categories')->orderBy('name')->get();

        return view('margins::livewire.margin-report', compact('rows', 'summary', 'categories'));
    }

    public function getRowsForExport(): \Illuminate\Support\Collection
    {
        return $this->buildQuery();
    }
}
