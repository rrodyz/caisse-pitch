<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained()->restrictOnDelete();
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->decimal('opening_amount', 12, 2)->default(0)->comment('Fonds de caisse à l\'ouverture');
            $table->decimal('closing_amount', 12, 2)->nullable()->comment('Montant constaté à la clôture');
            $table->decimal('expected_amount', 12, 2)->nullable()->comment('Calculé : ouverture + recettes espèces');
            $table->decimal('gap', 12, 2)->nullable()->comment('closing - expected');
            $table->text('notes_opening')->nullable();
            $table->text('notes_closing')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_sessions');
        Schema::dropIfExists('cash_registers');
    }
};
