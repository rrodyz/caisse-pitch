<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Categories\app\Models\Category;
use Modules\CashRegisters\app\Models\CashRegister;
use Modules\Products\app\Enums\ProductUnit;
use Modules\Products\app\Models\Product;
use Modules\Suppliers\app\Models\Supplier;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->error('DemoDataSeeder refusé en production — contient des données fictives et des comptes avec mots de passe connus.');
            return;
        }

        $this->seedCategories();
        $this->seedProducts();
        $this->seedSuppliers();
        $this->seedCashRegisters();
    }

    // ── Catégories ────────────────────────────────────────────────────────────

    private function seedCategories(): void
    {
        $cats = [
            ['name' => 'Bières & Pressions',  'color' => '#f59e0b', 'pos_order' => 1, 'description' => 'Bières locales et importées, pression'],
            ['name' => 'Spiritueux',           'color' => '#ef4444', 'pos_order' => 2, 'description' => 'Whiskies, vodkas, rhums, gins, tequilas'],
            ['name' => 'Cocktails',            'color' => '#ec4899', 'pos_order' => 3, 'description' => 'Cocktails maison avec et sans alcool'],
            ['name' => 'Vins & Champagnes',   'color' => '#8b5cf6', 'pos_order' => 4, 'description' => 'Vins rouges, blancs, rosés et champagnes'],
            ['name' => 'Softs & Jus',         'color' => '#10b981', 'pos_order' => 5, 'description' => 'Sodas, jus de fruits, boissons énergisantes'],
            ['name' => 'Eaux',                'color' => '#06b6d4', 'pos_order' => 6, 'description' => 'Eaux plates et gazeuses'],
            ['name' => 'En-cas & Snacks',     'color' => '#f97316', 'pos_order' => 7, 'description' => 'Snacks, chips, sandwichs'],
        ];

        foreach ($cats as $data) {
            Category::firstOrCreate(['name' => $data['name']], $data + ['is_active' => true]);
        }
    }

    // ── Produits ─────────────────────────────────────────────────────────────

    private function seedProducts(): void
    {
        $catId = fn(string $name) => Category::where('name', $name)->value('id');

        $products = [
            // ── Bières & Pressions ──────────────────────────────────────────
            ['code' => 'BIE-001', 'name' => 'Bière Pression (demi)',     'cat' => 'Bières & Pressions',  'unit' => 'verre',    'buy' => 300,   'sell' => 1000,  'stock' => 200, 'min' => 20],
            ['code' => 'BIE-002', 'name' => 'Heineken 33cl',             'cat' => 'Bières & Pressions',  'unit' => 'bouteille','buy' => 500,   'sell' => 1500,  'stock' => 120, 'min' => 24],
            ['code' => 'BIE-003', 'name' => 'Flag 65cl',                 'cat' => 'Bières & Pressions',  'unit' => 'bouteille','buy' => 600,   'sell' => 1500,  'stock' => 96,  'min' => 24],
            ['code' => 'BIE-004', 'name' => 'Guinness Smooth 50cl',      'cat' => 'Bières & Pressions',  'unit' => 'bouteille','buy' => 800,   'sell' => 2000,  'stock' => 48,  'min' => 12],
            ['code' => 'BIE-005', 'name' => 'Desperados 33cl',           'cat' => 'Bières & Pressions',  'unit' => 'bouteille','buy' => 900,   'sell' => 2500,  'stock' => 60,  'min' => 12],
            ['code' => 'BIE-006', 'name' => 'Corona Extra 35,5cl',       'cat' => 'Bières & Pressions',  'unit' => 'bouteille','buy' => 1000,  'sell' => 2500,  'stock' => 48,  'min' => 12],

            // ── Spiritueux ──────────────────────────────────────────────────
            ['code' => 'SPR-001', 'name' => 'Jack Daniel\'s (verre)',     'cat' => 'Spiritueux',          'unit' => 'verre',    'buy' => 2500,  'sell' => 5000,  'stock' => 0,   'min' => 1],
            ['code' => 'SPR-002', 'name' => 'Hennessy VSOP (verre)',      'cat' => 'Spiritueux',          'unit' => 'verre',    'buy' => 4000,  'sell' => 8000,  'stock' => 0,   'min' => 1],
            ['code' => 'SPR-003', 'name' => 'Vodka Absolut (verre)',      'cat' => 'Spiritueux',          'unit' => 'verre',    'buy' => 1500,  'sell' => 3500,  'stock' => 0,   'min' => 1],
            ['code' => 'SPR-004', 'name' => 'Rhum Bacardi (verre)',       'cat' => 'Spiritueux',          'unit' => 'verre',    'buy' => 1200,  'sell' => 3000,  'stock' => 0,   'min' => 1],
            ['code' => 'SPR-005', 'name' => 'Gin Gordon\'s (verre)',      'cat' => 'Spiritueux',          'unit' => 'verre',    'buy' => 1500,  'sell' => 3500,  'stock' => 0,   'min' => 1],
            ['code' => 'SPR-006', 'name' => 'Tequila Jose Cuervo (verre)','cat' => 'Spiritueux',          'unit' => 'verre',    'buy' => 1800,  'sell' => 4000,  'stock' => 0,   'min' => 1],
            ['code' => 'SPR-007', 'name' => 'Cognac Remy Martin (verre)', 'cat' => 'Spiritueux',          'unit' => 'verre',    'buy' => 3500,  'sell' => 7000,  'stock' => 0,   'min' => 1],
            ['code' => 'SPR-008', 'name' => 'Jack Daniel\'s 70cl',        'cat' => 'Spiritueux',          'unit' => 'bouteille','buy' => 22000, 'sell' => 45000, 'stock' => 6,   'min' => 2],
            ['code' => 'SPR-009', 'name' => 'Hennessy VSOP 70cl',         'cat' => 'Spiritueux',          'unit' => 'bouteille','buy' => 35000, 'sell' => 70000, 'stock' => 4,   'min' => 1],

            // ── Cocktails ────────────────────────────────────────────────────
            ['code' => 'CKT-001', 'name' => 'Mojito',                    'cat' => 'Cocktails',           'unit' => 'verre',    'buy' => 1200,  'sell' => 3500,  'stock' => 0,   'min' => 0],
            ['code' => 'CKT-002', 'name' => 'Sex on the Beach',          'cat' => 'Cocktails',           'unit' => 'verre',    'buy' => 1200,  'sell' => 3500,  'stock' => 0,   'min' => 0],
            ['code' => 'CKT-003', 'name' => 'Piña Colada',               'cat' => 'Cocktails',           'unit' => 'verre',    'buy' => 1200,  'sell' => 3500,  'stock' => 0,   'min' => 0],
            ['code' => 'CKT-004', 'name' => 'Cosmopolitan',              'cat' => 'Cocktails',           'unit' => 'verre',    'buy' => 1500,  'sell' => 4000,  'stock' => 0,   'min' => 0],
            ['code' => 'CKT-005', 'name' => 'Long Island Ice Tea',       'cat' => 'Cocktails',           'unit' => 'verre',    'buy' => 2000,  'sell' => 5000,  'stock' => 0,   'min' => 0],
            ['code' => 'CKT-006', 'name' => 'Daiquiri Fraise',           'cat' => 'Cocktails',           'unit' => 'verre',    'buy' => 1200,  'sell' => 3500,  'stock' => 0,   'min' => 0],
            ['code' => 'CKT-007', 'name' => 'Margarita',                 'cat' => 'Cocktails',           'unit' => 'verre',    'buy' => 1500,  'sell' => 4000,  'stock' => 0,   'min' => 0],

            // ── Vins & Champagnes ────────────────────────────────────────────
            ['code' => 'VIN-001', 'name' => 'Vin Rouge (verre)',          'cat' => 'Vins & Champagnes',  'unit' => 'verre',    'buy' => 800,   'sell' => 2000,  'stock' => 0,   'min' => 0],
            ['code' => 'VIN-002', 'name' => 'Vin Blanc (verre)',          'cat' => 'Vins & Champagnes',  'unit' => 'verre',    'buy' => 800,   'sell' => 2000,  'stock' => 0,   'min' => 0],
            ['code' => 'VIN-003', 'name' => 'Vin Rosé (verre)',           'cat' => 'Vins & Champagnes',  'unit' => 'verre',    'buy' => 800,   'sell' => 2000,  'stock' => 0,   'min' => 0],
            ['code' => 'VIN-004', 'name' => 'Moët & Chandon 75cl',       'cat' => 'Vins & Champagnes',  'unit' => 'bouteille','buy' => 25000, 'sell' => 55000, 'stock' => 6,   'min' => 2],
            ['code' => 'VIN-005', 'name' => 'G.H.Mumm Champagne 75cl',   'cat' => 'Vins & Champagnes',  'unit' => 'bouteille','buy' => 20000, 'sell' => 45000, 'stock' => 4,   'min' => 2],
            ['code' => 'VIN-006', 'name' => 'Rosé Minuty 75cl',          'cat' => 'Vins & Champagnes',  'unit' => 'bouteille','buy' => 12000, 'sell' => 25000, 'stock' => 8,   'min' => 2],

            // ── Softs & Jus ──────────────────────────────────────────────────
            ['code' => 'SOF-001', 'name' => 'Coca-Cola 33cl',            'cat' => 'Softs & Jus',        'unit' => 'canette',  'buy' => 250,   'sell' => 1000,  'stock' => 144, 'min' => 24],
            ['code' => 'SOF-002', 'name' => 'Fanta Orange 33cl',         'cat' => 'Softs & Jus',        'unit' => 'canette',  'buy' => 250,   'sell' => 1000,  'stock' => 96,  'min' => 24],
            ['code' => 'SOF-003', 'name' => 'Sprite 33cl',               'cat' => 'Softs & Jus',        'unit' => 'canette',  'buy' => 250,   'sell' => 1000,  'stock' => 96,  'min' => 24],
            ['code' => 'SOF-004', 'name' => 'Red Bull 25cl',             'cat' => 'Softs & Jus',        'unit' => 'canette',  'buy' => 700,   'sell' => 2000,  'stock' => 48,  'min' => 12],
            ['code' => 'SOF-005', 'name' => 'Jus de Mangue (verre)',     'cat' => 'Softs & Jus',        'unit' => 'verre',    'buy' => 300,   'sell' => 1000,  'stock' => 0,   'min' => 0],
            ['code' => 'SOF-006', 'name' => 'Jus de Gingembre (verre)', 'cat' => 'Softs & Jus',        'unit' => 'verre',    'buy' => 300,   'sell' => 1000,  'stock' => 0,   'min' => 0],
            ['code' => 'SOF-007', 'name' => 'Tonic 20cl',                'cat' => 'Softs & Jus',        'unit' => 'bouteille','buy' => 300,   'sell' => 800,   'stock' => 60,  'min' => 12],

            // ── Eaux ─────────────────────────────────────────────────────────
            ['code' => 'EAU-001', 'name' => 'Eau Plate 50cl',            'cat' => 'Eaux',               'unit' => 'bouteille','buy' => 150,   'sell' => 500,   'stock' => 120, 'min' => 24],
            ['code' => 'EAU-002', 'name' => 'Eau Gazeuse Perrier 33cl',  'cat' => 'Eaux',               'unit' => 'bouteille','buy' => 400,   'sell' => 1000,  'stock' => 48,  'min' => 12],
            ['code' => 'EAU-003', 'name' => 'Eau Plate 1,5L',            'cat' => 'Eaux',               'unit' => 'bouteille','buy' => 300,   'sell' => 800,   'stock' => 48,  'min' => 12],

            // ── En-cas & Snacks ──────────────────────────────────────────────
            ['code' => 'SNA-001', 'name' => 'Chips Pringles',            'cat' => 'En-cas & Snacks',    'unit' => 'unité',    'buy' => 500,   'sell' => 1500,  'stock' => 30,  'min' => 10],
            ['code' => 'SNA-002', 'name' => 'Cacahuètes grillées',       'cat' => 'En-cas & Snacks',    'unit' => 'unité',    'buy' => 200,   'sell' => 700,   'stock' => 50,  'min' => 10],
            ['code' => 'SNA-003', 'name' => 'Mix noix & fruits secs',    'cat' => 'En-cas & Snacks',    'unit' => 'unité',    'buy' => 500,   'sell' => 1500,  'stock' => 20,  'min' => 5],
            ['code' => 'SNA-004', 'name' => 'Sandwich Club poulet',      'cat' => 'En-cas & Snacks',    'unit' => 'unité',    'buy' => 1500,  'sell' => 4000,  'stock' => 10,  'min' => 2],
            ['code' => 'SNA-005', 'name' => 'Plateau de fromages',       'cat' => 'En-cas & Snacks',    'unit' => 'unité',    'buy' => 3000,  'sell' => 7500,  'stock' => 5,   'min' => 2],
        ];

        foreach ($products as $p) {
            Product::firstOrCreate(['code' => $p['code']], [
                'name'           => $p['name'],
                'category_id'    => $catId($p['cat']),
                'purchase_price' => $p['buy'],
                'selling_price'  => $p['sell'],
                'stock_quantity' => $p['stock'],
                'min_stock'      => $p['min'],
                'unit'           => ProductUnit::from($p['unit']),
                'is_active'      => true,
            ]);
        }
    }

    // ── Fournisseurs ──────────────────────────────────────────────────────────

    private function seedSuppliers(): void
    {
        $suppliers = [
            [
                'name'         => 'SOBEBRA',
                'phone'        => '+229 21 30 12 34',
                'email'        => 'commandes@sobebra.bj',
                'address'      => 'Zone Industrielle, Cotonou',
                'contact_name' => 'Jean-Pierre Ahounou',
                'notes'        => 'Brasseries du Bénin — bières locales, softs',
            ],
            [
                'name'         => 'CFAO Beverages',
                'phone'        => '+229 21 31 45 67',
                'email'        => 'ventes@cfaobev.bj',
                'address'      => 'Avenue Jean-Paul II, Cotonou',
                'contact_name' => 'Marie Dossou',
                'notes'        => 'Spiritueux importés — Hennessy, Moët, Jack Daniel\'s',
            ],
            [
                'name'         => 'Distribution Locale SA',
                'phone'        => '+229 97 45 23 11',
                'email'        => null,
                'address'      => 'Marché St-Michel, Cotonou',
                'contact_name' => 'Koffi Mensah',
                'notes'        => 'Fruits frais, softs, eaux',
            ],
            [
                'name'         => 'Groupe Grands Crus',
                'phone'        => '+229 21 38 90 12',
                'email'        => 'contact@grandscrus-bj.com',
                'address'      => 'Fidjrossè, Cotonou',
                'contact_name' => 'Sophie Garnier',
                'notes'        => 'Vins et champagnes de qualité',
            ],
        ];

        foreach ($suppliers as $data) {
            Supplier::firstOrCreate(['name' => $data['name']], $data + ['is_active' => true]);
        }
    }

    // ── Caisses ───────────────────────────────────────────────────────────────

    private function seedCashRegisters(): void
    {
        $registers = [
            ['name' => 'Caisse Bar Principal',  'location' => 'Bar principal — rez-de-chaussée'],
            ['name' => 'Caisse VIP Lounge',     'location' => 'Espace VIP — 1er étage'],
            ['name' => 'Caisse Entrée',         'location' => 'Accueil & entrée'],
        ];

        foreach ($registers as $data) {
            CashRegister::firstOrCreate(['name' => $data['name']], $data + ['is_active' => true]);
        }
    }

}
