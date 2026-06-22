<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // products : stock_quantity/min_stock INT → DECIMAL(10,4)
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('stock_quantity', 10, 4)->default(0)->change();
            $table->decimal('min_stock', 10, 4)->default(0)->change();
        });

        // purchase_items : quantity DECIMAL(10,3) → DECIMAL(10,4)
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 4)->default(1)->change();
        });

        // sale_items : ajouter timestamps si absents
        if (! Schema::hasColumn('sale_items', 'created_at')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->timestamps();
            });
        }

        // cash_sessions : opened_by NOT NULL → NULLABLE (permet suppression d'utilisateurs)
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('opened_by')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock_quantity')->default(0)->change();
            $table->integer('min_stock')->default(0)->change();
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 3)->default(1)->change();
        });
    }
};
