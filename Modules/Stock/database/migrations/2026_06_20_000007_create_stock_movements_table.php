<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->enum('type', [
                'purchase_in',
                'sale_out',
                'loss',
                'break',
                'gift',
                'inventory_adjustment',
                'manual_in',
                'manual_out',
            ]);
            $table->decimal('quantity', 10, 4);
            $table->decimal('quantity_before', 10, 4);
            $table->decimal('quantity_after', 10, 4);
            $table->decimal('unit_cost', 10, 2)->nullable();
            // polymorphic reference : Purchase, Sale, Loss, Inventory…
            $table->nullableMorphs('reference');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
