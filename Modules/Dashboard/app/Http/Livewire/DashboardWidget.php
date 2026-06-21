<?php

namespace Modules\Dashboard\app\Http\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardWidget extends Component
{
    // Refreshes every 60 seconds via wire:poll
    protected $listeners = ['$refresh'];

    public function render()
    {
        $today     = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $month     = now()->startOfMonth()->toDateString();

        // ── KPIs today ──────────────────────────────────────────────────────
        $todayStats = DB::table('sales')
            ->where('status', 'completed')
            ->whereDate('created_at', $today)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount),0) as total,
                COALESCE(AVG(total_amount),0) as avg_ticket,
                COALESCE(SUM(discount_amount),0) as discounts')
            ->first();

        $yesterdayTotal = DB::table('sales')
            ->where('status', 'completed')
            ->whereDate('created_at', $yesterday)
            ->sum('total_amount');

        $monthTotal = DB::table('sales')
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $month)
            ->sum('total_amount');

        $todayCancelled = DB::table('sales')
            ->where('status', 'cancelled')
            ->whereDate('created_at', $today)
            ->count();

        // ── Caisse ouverte ───────────────────────────────────────────────────
        $openSession = DB::table('cash_sessions as cs')
            ->join('cash_registers as cr', 'cr.id', '=', 'cs.cash_register_id')
            ->leftJoin('users as u', 'u.id', '=', 'cs.opened_by')
            ->where('cs.status', 'open')
            ->selectRaw('cs.id, cs.opening_amount, cs.opened_at, cr.name as register_name, ' . $this->nameExpr() . ' as cashier_name')
            ->first();

        $cashSalesToday = $openSession
            ? DB::table('sales')
                ->where('status', 'completed')
                ->where('payment_mode', 'cash')
                ->where('cash_session_id', $openSession->id)
                ->sum('total_amount')
            : 0;

        // ── Top produits aujourd'hui ─────────────────────────────────────────
        $topProducts = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->where('s.status', 'completed')
            ->whereDate('s.created_at', $today)
            ->whereNotNull('si.product_id')
            ->selectRaw('si.product_name, SUM(si.quantity) as qty, SUM(si.total_price) as revenue')
            ->groupBy('si.product_id', 'si.product_name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        // ── Alertes stock ────────────────────────────────────────────────────
        $stockAlerts = DB::table('products')
            ->where('is_active', true)
            ->whereRaw('stock_quantity <= min_stock')
            ->selectRaw('id, name, unit, stock_quantity, min_stock,
                CASE WHEN stock_quantity <= 0 THEN "rupture" ELSE "bas" END as status')
            ->orderBy('stock_quantity')
            ->limit(8)
            ->get();

        $stockAlertCount = DB::table('products')
            ->where('is_active', true)
            ->whereRaw('stock_quantity <= min_stock')
            ->count();

        // ── Pertes du jour ───────────────────────────────────────────────────
        $todayLosses = DB::table('losses')
            ->whereDate('created_at', $today)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_cost),0) as total_cost')
            ->first();

        // ── Encours crédits ──────────────────────────────────────────────────
        $creditStats = DB::table('customers')
            ->where('is_active', true)
            ->where('current_credit', '>', 0)
            ->selectRaw('COUNT(*) as count, SUM(current_credit) as total')
            ->first();

        // ── Ventes récentes ──────────────────────────────────────────────────
        $recentSales = DB::table('sales as s')
            ->leftJoin('users as u', 'u.id', '=', 's.served_by')
            ->leftJoin('customers as c', 'c.id', '=', 's.customer_id')
            ->selectRaw('s.id, s.number, s.total_amount, s.status, s.payment_mode, s.created_at, ' . $this->nameExpr() . ' as cashier_name, c.name as customer_name')
            ->orderByDesc('s.created_at')
            ->limit(10)
            ->get();

        // ── Évolution CA 7 jours ─────────────────────────────────────────────
        $weekSales = DB::table('sales')
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subDays(6)->toDateString())
            ->selectRaw('DATE(created_at) as day, COALESCE(SUM(total_amount),0) as total, COUNT(*) as count')
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get()
            ->keyBy('day');

        // Fill missing days
        $last7Days = collect(range(6, 0))->map(fn($d) => now()->subDays($d)->toDateString());
        $weekChart = $last7Days->map(fn($day) => [
            'day'   => $day,
            'label' => now()->parse($day)->format('d/m'),
            'total' => $weekSales->get($day)?->total ?? 0,
            'count' => $weekSales->get($day)?->count ?? 0,
        ]);

        $weekMax = $weekChart->max('total') ?: 1;

        return view('dashboard::livewire.dashboard-widget', compact(
            'todayStats', 'yesterdayTotal', 'monthTotal', 'todayCancelled',
            'openSession', 'cashSalesToday',
            'topProducts', 'stockAlerts', 'stockAlertCount',
            'todayLosses', 'creditStats',
            'recentSales', 'weekChart', 'weekMax'
        ));
    }

    private function nameExpr(string $alias = 'u'): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "({$alias}.first_name || ' ' || {$alias}.last_name)"
            : "CONCAT({$alias}.first_name, ' ', {$alias}.last_name)";
    }
}
