<?php

namespace Modules\Reports\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Settings\app\Models\Setting;

class LossReportPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $dateFrom = $request->get('dateFrom', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('dateTo',   now()->format('Y-m-d'));
        $lossType = $request->get('lossType', '');

        $rows = $this->buildRows($dateFrom, $dateTo, $lossType);
        $summary = $this->buildSummary($dateFrom, $dateTo);
        $settings = Setting::current();

        $typeLabel = match($lossType) {
            'loss'  => 'Pertes',
            'break' => 'Casses',
            'gift'  => 'Offerts / Gratuits',
            default => 'Tous types',
        };

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports::loss-report-pdf', compact(
            'rows', 'summary', 'settings',
            'dateFrom', 'dateTo', 'lossType', 'typeLabel'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('rapport_pertes_' . now()->format('Ymd_His') . '.pdf');
    }

    private function buildRows(string $dateFrom, string $dateTo, string $lossType): \Illuminate\Support\Collection
    {
        return DB::table('losses as l')
            ->join('products as p', 'p.id', '=', 'l.product_id')
            ->leftJoin('users as u', 'u.id', '=', 'l.declared_by')
            ->when($dateFrom, fn($q) => $q->whereDate('l.created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('l.created_at', '<=', $dateTo))
            ->when($lossType, fn($q) => $q->where('l.type', $lossType))
            ->selectRaw('l.id, l.type, l.quantity, l.unit_cost, l.total_cost, l.reason,
                l.created_at, p.name as product_name, p.unit as product_unit,
                CONCAT(u.first_name, " ", u.last_name) as user_name')
            ->orderByDesc('l.created_at')
            ->get();
    }

    private function buildSummary(string $dateFrom, string $dateTo): array
    {
        $base = DB::table('losses')
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo));

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
}
