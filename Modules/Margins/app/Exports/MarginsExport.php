<?php

namespace Modules\Margins\app\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MarginsExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        private string $dateFrom,
        private string $dateTo,
        private string $groupBy,
        private ?int   $categoryId,
    ) {}

    public function collection(): Collection
    {
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
                    COALESCE(c.name, "Sans catégorie") as Catégorie,
                    COUNT(DISTINCT p.id) as Produits,
                    SUM(si.quantity) as "Qté vendue",
                    SUM(si.total_price) as "CA (FCFA)",
                    SUM(si.quantity * COALESCE(r.cost_price, p.purchase_price, 0)) as "Coût total (FCFA)",
                    SUM(si.total_price) - SUM(si.quantity * COALESCE(r.cost_price, p.purchase_price, 0)) as "Marge brute (FCFA)",
                    ROUND((SUM(si.total_price) - SUM(si.quantity * COALESCE(r.cost_price, p.purchase_price, 0))) / NULLIF(SUM(si.total_price), 0) * 100, 1) as "Taux marge (%)"
                ')
                ->groupBy('c.id', 'c.name')
                ->orderByDesc('CA (FCFA)')
                ->get();
        } else {
            $rows = $base->selectRaw('
                    p.name as Produit,
                    COALESCE(c.name, "Sans catégorie") as Catégorie,
                    SUM(si.quantity) as "Qté vendue",
                    SUM(si.total_price) as "CA (FCFA)",
                    SUM(si.quantity * COALESCE(r.cost_price, p.purchase_price, 0)) as "Coût total (FCFA)",
                    SUM(si.total_price) - SUM(si.quantity * COALESCE(r.cost_price, p.purchase_price, 0)) as "Marge brute (FCFA)",
                    ROUND((SUM(si.total_price) - SUM(si.quantity * COALESCE(r.cost_price, p.purchase_price, 0))) / NULLIF(SUM(si.total_price), 0) * 100, 1) as "Taux marge (%)"
                ')
                ->groupBy('p.id', 'p.name', 'c.name')
                ->orderByDesc('CA (FCFA)')
                ->get();
        }

        return $rows;
    }

    public function headings(): array
    {
        if ($this->groupBy === 'category') {
            return ['Catégorie', 'Produits', 'Qté vendue', 'CA (FCFA)', 'Coût total (FCFA)', 'Marge brute (FCFA)', 'Taux marge (%)'];
        }
        return ['Produit', 'Catégorie', 'Qté vendue', 'CA (FCFA)', 'Coût total (FCFA)', 'Marge brute (FCFA)', 'Taux marge (%)'];
    }

    public function title(): string
    {
        return 'Marges ' . $this->dateFrom . ' → ' . $this->dateTo;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
