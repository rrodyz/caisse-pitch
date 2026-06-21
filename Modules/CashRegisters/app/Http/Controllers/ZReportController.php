<?php

namespace Modules\CashRegisters\app\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\CashRegisters\app\Models\CashSession;
use Modules\Settings\app\Models\Setting;

class ZReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:view-cash-sessions']);
    }

    public function show(int $id)
    {
        $session  = CashSession::with('cashRegister', 'openedBy', 'closedBy')->findOrFail($id);
        $stats    = $this->buildStats($session);
        $settings = Setting::current();

        return view('cashregisters::z-report', compact('session', 'stats', 'settings'));
    }

    public function pdf(int $id)
    {
        $session  = CashSession::with('cashRegister', 'openedBy', 'closedBy')->findOrFail($id);
        $stats    = $this->buildStats($session);
        $settings = Setting::current();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'cashregisters::z-report-pdf',
            compact('session', 'stats', 'settings')
        )->setPaper([0, 0, 226.77, 841.89]); // 80mm × 297mm in points

        return $pdf->stream('rapport-z-' . $session->id . '.pdf');
    }

    // ── Calcul des statistiques ──────────────────────────────────────────────

    private function buildStats(CashSession $session): array
    {
        $saleClass = \Modules\Sales\app\Models\Sale::class;

        if (! class_exists($saleClass)) {
            return $this->emptyStats($session);
        }

        $sales = $saleClass::where('cash_session_id', $session->id)->get();

        $completed  = $sales->where('status', 'completed');
        $cancelled  = $sales->where('status', 'cancelled');

        // Totaux par mode de paiement
        $byMode = [];
        foreach (['cash', 'card', 'mobile_money', 'orange_money', 'moov_money', 'wave', 'credit'] as $mode) {
            $byMode[$mode] = (float) $completed->where('payment_mode', $mode)->sum('total_amount');
        }

        $totalSales    = (float) $completed->sum('total_amount');
        $totalDiscount = (float) $completed->sum('discount_amount');
        $totalCash     = $byMode['cash'];

        $expectedClosing = $session->opening_amount + $totalCash;
        $gap             = $session->closing_amount !== null
            ? $session->closing_amount - $expectedClosing
            : null;

        return [
            'sales_count'     => $completed->count(),
            'cancelled_count' => $cancelled->count(),
            'by_mode'         => $byMode,
            'total_sales'     => $totalSales,
            'total_discount'  => $totalDiscount,
            'cash_sales'      => $totalCash,
            'expected_closing'=> $expectedClosing,
            'gap'             => $gap,
            'cancelled_list'  => $cancelled->load('items', 'servedBy')->all(),
        ];
    }

    private function emptyStats(CashSession $session): array
    {
        return [
            'sales_count'     => 0,
            'cancelled_count' => 0,
            'by_mode'         => ['cash' => 0, 'card' => 0, 'mobile_money' => 0, 'orange_money' => 0, 'moov_money' => 0, 'wave' => 0, 'credit' => 0],
            'total_sales'     => 0,
            'total_discount'  => 0,
            'cash_sales'      => 0,
            'expected_closing'=> $session->opening_amount,
            'gap'             => null,
            'cancelled_list'  => [],
        ];
    }
}
