<?php

namespace Modules\Reports\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Settings\app\Models\Setting;

class StockReportPdfController extends Controller
{
    public function __invoke(Request $request)
    {

        $view       = $request->get('view', 'valuation');
        $search     = $request->get('search', '');
        $categoryId = $request->get('categoryId');
        $sortBy     = $request->get('sortBy', 'value');
        $dateFrom   = $request->get('dateFrom', '');
        $dateTo     = $request->get('dateTo', '');

        $data = match($view) {
            'movements' => $this->buildMovements($search, $dateFrom, $dateTo, $request->get('filterType', '')),
            'alerts'    => $this->buildAlerts($search, $categoryId),
            default     => $this->buildValuation($search, $categoryId, $sortBy),
        };

        $settings = Setting::current();
        $title = match($view) {
            'movements' => 'Mouvements de stock',
            'alerts'    => 'Alertes stock',
            default     => 'Valorisation du stock',
        };

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports::stock-report-pdf', compact(
            'data', 'view', 'title', 'settings',
            'search', 'dateFrom', 'dateTo'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('rapport_stock_' . now()->format('Ymd_His') . '.pdf');
    }

    private function buildValuation(string $search, ?int $categoryId, string $sortBy): array
    {
        $orderCol = match($sortBy) {
            'qty'  => 'p.stock_quantity',
            'name' => 'p.name',
            default => null,
        };

        $q = DB::table('products as p')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->where('p.is_active', true)
            ->when($search, fn($q) => $q->where('p.name', 'like', "%{$search}%"))
            ->when($categoryId, fn($q) => $q->where('p.category_id', $categoryId))
            ->selectRaw('
                p.id, p.name, p.unit, p.stock_quantity, p.min_stock, p.purchase_price,
                p.stock_quantity * p.purchase_price AS stock_value,
                c.name AS category_name,
                CASE WHEN p.stock_quantity <= 0 THEN "rupture"
                     WHEN p.stock_quantity <= p.min_stock THEN "bas"
                     ELSE "ok" END AS stock_status
            ');

        $orderCol
            ? $q->orderByDesc($orderCol)
            : $q->orderByRaw('p.stock_quantity * p.purchase_price DESC');

        $rows = $q->get();

        $summary = [
            'total_value'     => $rows->sum('stock_value'),
            'total_products'  => $rows->count(),
            'low_stock_count' => $rows->where('stock_status', 'bas')->count(),
            'out_stock_count' => $rows->where('stock_status', 'rupture')->count(),
        ];

        return compact('rows', 'summary');
    }

    private function buildMovements(string $search, string $dateFrom, string $dateTo, string $filterType): array
    {
        $nameExpr = "CONCAT(u.first_name, ' ', u.last_name)";

        $rows = DB::table('stock_movements as sm')
            ->join('products as p', 'p.id', '=', 'sm.product_id')
            ->leftJoin('users as u', 'u.id', '=', 'sm.user_id')
            ->when($search, fn($q) => $q->where('p.name', 'like', "%{$search}%"))
            ->when($filterType, fn($q) => $q->where('sm.type', $filterType))
            ->when($dateFrom, fn($q) => $q->whereDate('sm.created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('sm.created_at', '<=', $dateTo))
            ->selectRaw("sm.*, p.name as product_name, {$nameExpr} as user_name")
            ->orderByDesc('sm.created_at')
            ->limit(500)
            ->get();

        return ['rows' => $rows];
    }

    private function buildAlerts(string $search, ?int $categoryId): array
    {
        $rows = DB::table('products as p')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->where('p.is_active', true)
            ->whereRaw('p.stock_quantity <= p.min_stock')
            ->when($search, fn($q) => $q->where('p.name', 'like', "%{$search}%"))
            ->when($categoryId, fn($q) => $q->where('p.category_id', $categoryId))
            ->selectRaw('p.id, p.name, p.unit, p.stock_quantity, p.min_stock,
                c.name AS category_name,
                (p.min_stock - p.stock_quantity) AS shortage')
            ->orderBy('p.stock_quantity')
            ->get();

        return ['rows' => $rows];
    }
}
