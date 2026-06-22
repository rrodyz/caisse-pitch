<?php

namespace Modules\Reports\app\Http\Livewire;

use App\Support\RateLimitGuard;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Reports\app\Exports\StockReportExport;

class StockReport extends Component
{
    use WithPagination;

    public string $view       = 'valuation'; // valuation | movements | alerts
    public string $search     = '';
    public string $filterType = '';
    public string $dateFrom   = '';
    public string $dateTo     = '';
    public ?int   $categoryId = null;
    public string $sortBy     = 'value'; // value | qty | name

    public function updatedView(): void     { $this->resetPage(); }
    public function updatedSearch(): void   { $this->resetPage(); }
    public function updatedFilterType(): void { $this->resetPage(); }

    public function export(): ?\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('export-reports');

        if (! RateLimitGuard::enforce('exports', fn (int $seconds) => $this->addError(
            'export',
            "Limite d'exports atteinte. Réessayez dans {$seconds} secondes."
        ))) {
            return null;
        }

        return Excel::download(
            new StockReportExport($this->view, $this->search, $this->categoryId),
            'rapport_stock_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function render()
    {
        $this->authorize('view-reports');

        $categories = DB::table('categories')->orderBy('name')->get();

        $data = match($this->view) {
            'valuation' => $this->buildValuation(),
            'movements' => $this->buildMovements(),
            'alerts'    => $this->buildAlerts(),
            default     => collect(),
        };

        return view('reports::livewire.stock-report', array_merge(
            compact('categories'),
            ['data' => $data]
        ));
    }

    private function buildValuation(): array
    {
        $orderCol = match($this->sortBy) {
            'qty'  => 'p.stock_quantity',
            'name' => 'p.name',
            default => 'stock_value',
        };

        $rows = DB::table('products as p')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->where('p.is_active', true)
            ->when($this->search, fn($q) => $q->where('p.name', 'like', "%{$this->search}%"))
            ->when($this->categoryId, fn($q) => $q->where('p.category_id', $this->categoryId))
            ->selectRaw('
                p.id, p.name, p.unit, p.stock_quantity, p.min_stock, p.purchase_price,
                p.stock_quantity * p.purchase_price AS stock_value,
                c.name AS category_name,
                CASE WHEN p.stock_quantity <= 0 THEN "rupture"
                     WHEN p.stock_quantity <= p.min_stock THEN "bas"
                     ELSE "ok" END AS stock_status
            ')
            ->orderByDesc($orderCol)
            ->get();

        $summary = [
            'total_value'      => $rows->sum('stock_value'),
            'total_products'   => $rows->count(),
            'low_stock_count'  => $rows->where('stock_status', 'bas')->count(),
            'out_stock_count'  => $rows->where('stock_status', 'rupture')->count(),
        ];

        return compact('rows', 'summary');
    }

    private function buildMovements(): array
    {
        $rows = DB::table('stock_movements as sm')
            ->join('products as p', 'p.id', '=', 'sm.product_id')
            ->leftJoin('users as u', 'u.id', '=', 'sm.user_id')
            ->when($this->search, fn($q) => $q->where('p.name', 'like', "%{$this->search}%"))
            ->when($this->filterType, fn($q) => $q->where('sm.type', $this->filterType))
            ->when($this->dateFrom, fn($q) => $q->whereDate('sm.created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('sm.created_at', '<=', $this->dateTo))
            ->selectRaw('sm.*, p.name as product_name, ' . $this->nameExpr() . ' as user_name')
            ->orderByDesc('sm.created_at')
            ->paginate(30);

        return ['rows' => $rows];
    }

    private function nameExpr(string $alias = 'u'): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "({$alias}.first_name || ' ' || {$alias}.last_name)"
            : "CONCAT({$alias}.first_name, ' ', {$alias}.last_name)";
    }

    private function buildAlerts(): array
    {
        $rows = DB::table('products as p')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->where('p.is_active', true)
            ->whereRaw('p.stock_quantity <= p.min_stock')
            ->selectRaw('p.id, p.name, p.unit, p.stock_quantity, p.min_stock,
                c.name AS category_name,
                (p.min_stock - p.stock_quantity) AS shortage')
            ->orderBy('p.stock_quantity')
            ->get();

        return ['rows' => $rows];
    }
}
