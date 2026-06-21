<?php

namespace Modules\Reports\app\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        private string $dateFrom,
        private string $dateTo,
        private string $groupBy,
    ) {}

    public function collection(): Collection
    {
        $base = DB::table('sales as s')
            ->where('s.status', 'completed')
            ->when($this->dateFrom, fn($q) => $q->whereDate('s.created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('s.created_at', '<=', $this->dateTo));

        return match($this->groupBy) {
            'day' => $base
                ->selectRaw('DATE(s.created_at) as Jour, COUNT(*) as Transactions, SUM(s.total_amount) as "CA (FCFA)", SUM(s.discount_amount) as "Remises (FCFA)", AVG(s.total_amount) as "Ticket moyen"')
                ->groupByRaw('DATE(s.created_at)')
                ->orderByRaw('DATE(s.created_at)')
                ->get(),

            'payment_mode' => $base
                ->selectRaw('s.payment_mode as "Mode paiement", COUNT(*) as Transactions, SUM(s.total_amount) as "CA (FCFA)"')
                ->groupBy('s.payment_mode')
                ->orderByDesc('CA (FCFA)')
                ->get(),

            'product' => $base
                ->join('sale_items as si', 'si.sale_id', '=', 's.id')
                ->join('products as p', 'p.id', '=', 'si.product_id')
                ->selectRaw('p.name as Produit, SUM(si.quantity) as "Qté", SUM(si.total_price) as "CA (FCFA)"')
                ->groupBy('p.id', 'p.name')
                ->orderByDesc('CA (FCFA)')
                ->get(),

            'category' => $base
                ->join('sale_items as si', 'si.sale_id', '=', 's.id')
                ->join('products as p', 'p.id', '=', 'si.product_id')
                ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
                ->selectRaw('COALESCE(c.name, "Sans catégorie") as Catégorie, COUNT(DISTINCT s.id) as Transactions, SUM(si.total_price) as "CA (FCFA)"')
                ->groupBy('c.id', 'c.name')
                ->orderByDesc('CA (FCFA)')
                ->get(),

            default => collect(),
        };
    }

    public function headings(): array { return []; }

    public function title(): string
    {
        return 'Ventes ' . $this->dateFrom . ' → ' . $this->dateTo;
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
