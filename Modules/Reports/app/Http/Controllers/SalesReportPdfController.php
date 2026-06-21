<?php

namespace Modules\Reports\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Settings\app\Models\Setting;

class SalesReportPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $dateFrom = $request->get('dateFrom', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('dateTo',   now()->format('Y-m-d'));
        $groupBy  = $request->get('groupBy', 'day');

        $rows    = $this->buildRows($dateFrom, $dateTo, $groupBy);
        $summary = $this->buildSummary($dateFrom, $dateTo);
        $grandTotal = $rows->sum('total') ?: 1;

        $settings = Setting::current();
        $groupLabel = match($groupBy) {
            'day'          => 'Par jour',
            'product'      => 'Par produit',
            'category'     => 'Par catégorie',
            'payment_mode' => 'Par mode de paiement',
            default        => '',
        };

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports::sales-report-pdf', compact(
            'rows', 'summary', 'grandTotal', 'settings',
            'dateFrom', 'dateTo', 'groupBy', 'groupLabel'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('rapport_ventes_' . now()->format('Ymd_His') . '.pdf');
    }

    private function buildRows(string $dateFrom, string $dateTo, string $groupBy): \Illuminate\Support\Collection
    {
        $base = DB::table('sales as s')
            ->where('s.status', 'completed')
            ->when($dateFrom, fn($q) => $q->whereDate('s.created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('s.created_at', '<=', $dateTo));

        return match($groupBy) {
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

            default => collect(),
        };
    }

    private function buildSummary(string $dateFrom, string $dateTo): array
    {
        $base = DB::table('sales')
            ->where('status', 'completed')
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo));

        $r = (clone $base)->selectRaw(
            'COUNT(*) as cnt, SUM(total_amount) as total, SUM(discount_amount) as discounts, AVG(total_amount) as avg'
        )->first();

        $cancelledCount = DB::table('sales')
            ->where('status', 'cancelled')
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->count();

        return [
            'count'           => $r->cnt ?? 0,
            'total'           => $r->total ?? 0,
            'discounts'       => $r->discounts ?? 0,
            'avg_ticket'      => $r->avg ?? 0,
            'cancelled_count' => $cancelledCount,
        ];
    }
}
