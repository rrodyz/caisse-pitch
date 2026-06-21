<?php

namespace Modules\Tickets\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sales\app\Models\Sale;
use Modules\Settings\app\Models\Setting;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Affiche le ticket en HTML (nouvelle fenêtre POS).
     */
    public function show(int $id)
    {
        $user = auth()->user();
        if (! $user->can('print-tickets') && ! $user->can('reprint-tickets')) {
            abort(403);
        }

        $sale     = Sale::with('items.product', 'servedBy', 'cashSession.cashRegister')->findOrFail($id);
        $settings = Setting::current();

        return view('tickets::ticket', compact('sale', 'settings'));
    }

    /**
     * Génère le ticket en PDF thermique via Snappy.
     */
    public function pdf(int $id)
    {
        $user = auth()->user();
        if (! $user->can('print-tickets') && ! $user->can('reprint-tickets')) {
            abort(403);
        }

        $sale     = Sale::with('items.product', 'servedBy', 'cashSession.cashRegister')->findOrFail($id);
        $settings = Setting::current();

        $html = view('tickets::ticket-thermal', compact('sale', 'settings'))->render();

        $pdf = app('snappy.pdf.wrapper');
        $pdf->loadHTML($html);
        $pdf->setOption('page-width', '80mm');
        $pdf->setOption('page-height', '297mm'); // auto-height trick: very tall page
        $pdf->setOption('margin-top', '3mm');
        $pdf->setOption('margin-bottom', '3mm');
        $pdf->setOption('margin-left', '3mm');
        $pdf->setOption('margin-right', '3mm');
        $pdf->setOption('disable-smart-shrinking', true);
        $pdf->setOption('encoding', 'UTF-8');

        $filename = 'ticket-' . $sale->number . '.pdf';

        return $pdf->stream($filename);
    }
}
