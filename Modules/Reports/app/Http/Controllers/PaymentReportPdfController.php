<?php

namespace Modules\Reports\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Settings\app\Models\Setting;

class PaymentReportPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $dateFrom = $request->get('dateFrom', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('dateTo',   now()->format('Y-m-d'));

        $byMode = DB::table('sales')
            ->where('status', 'completed')
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->selectRaw('payment_mode, COUNT(*) as cnt, SUM(total_amount) as total, AVG(total_amount) as avg_ticket')
            ->groupBy('payment_mode')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => (object) array_merge((array) $r, [
                'label' => $this->modeLabel($r->payment_mode),
            ]));

        $totals = DB::table('sales')
            ->where('status', 'completed')
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->selectRaw('COUNT(*) as cnt, SUM(total_amount) as total')
            ->first();

        $settings = Setting::current();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports::payment-report-pdf', compact(
            'byMode', 'totals', 'settings', 'dateFrom', 'dateTo'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('rapport_paiements_' . now()->format('Ymd_His') . '.pdf');
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
}
