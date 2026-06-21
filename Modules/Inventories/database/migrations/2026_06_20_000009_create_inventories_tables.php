<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->enum('status', ['draft', 'in_progress', 'validated', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('theoretical_quantity', 10, 4)->comment('Stock système au snapshot');
            $table->decimal('counted_quantity', 10, 4)->nullable()->comment('Quantité comptée physiquement');
            $table->decimal('gap', 10, 4)->default(0)->comment('counted - theoretical');
            $table->decimal('unit_cost', 10, 2)->nullable()->comment('Prix achat snapshot');
            $table->decimal('gap_cost', 10, 2)->default(0)->comment('gap × unit_cost');
            $table->text('notes')->nullable();
            $table->unique(['inventory_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventories');
    }
};
