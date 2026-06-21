<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('losses', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['loss', 'break', 'gift']);
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 10, 4);
            $table->decimal('unit_cost', 10, 2)->nullable()->comment('Snapshot prix achat au moment de la saisie');
            $table->decimal('total_cost', 10, 2)->default(0)->comment('quantity × unit_cost');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('declared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('stock_movement_id')->nullable()->constrained('stock_movements')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('losses');
    }
};
