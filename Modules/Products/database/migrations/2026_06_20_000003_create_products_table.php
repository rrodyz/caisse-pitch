<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            // Calculés automatiquement à chaque save
            $table->decimal('margin', 10, 2)->default(0);
            $table->decimal('margin_rate', 8, 2)->default(0);  // (marge / prix achat) × 100
            $table->decimal('markup_rate', 8, 2)->default(0);  // (marge / prix vente) × 100
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock')->default(0);
            $table->enum('unit', ['bouteille', 'verre', 'canette', 'carton', 'unité'])->default('unité');
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('category_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
