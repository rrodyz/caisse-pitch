<?php

namespace Modules\Reports\app\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockReportExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        private string $view,
        private string $search,
        private ?int   $categoryId,
    ) {}

    public function collection(): Collection
    {
        if ($this->view === 'alerts') {
            return DB::table('products as p')
                ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
                ->where('p.is_active', true)
                ->whereRaw('p.stock_quantity <= p.min_stock')
                ->selectRaw('p.name as Produit, COALESCE(c.name,"—") as Catégorie, p.unit as Unité, p.stock_quantity as "Stock actuel", p.min_stock as "Stock mini", (p.min_stock - p.stock_quantity) as Manquant')
                ->orderBy('p.stock_quantity')
                ->get();
        }

        return DB::table('products as p')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->where('p.is_active', true)
            ->when($this->search, fn($q) => $q->where('p.name', 'like', "%{$this->search}%"))
            ->when($this->categoryId, fn($q) => $q->where('p.category_id', $this->categoryId))
            ->selectRaw('p.name as Produit, COALESCE(c.name,"—") as Catégorie, p.unit as Unité,
                p.stock_quantity as "Qté en stock", p.min_stock as "Stock mini",
                p.purchase_price as "Px achat (FCFA)",
                p.stock_quantity * p.purchase_price as "Valeur (FCFA)"')
            ->orderByRaw('p.stock_quantity * p.purchase_price DESC')
            ->get();
    }

    public function headings(): array { return []; }

    public function title(): string
    {
        return match($this->view) {
            'alerts'    => 'Alertes stock',
            default     => 'Valorisation stock ' . now()->format('d/m/Y'),
        };
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
