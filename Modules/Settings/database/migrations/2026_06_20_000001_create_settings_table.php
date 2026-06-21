<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('establishment_name')->default('Mon Établissement');
            $table->string('logo')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('currency', 10)->default('FCFA');
            $table->string('currency_code', 3)->default('XOF');
            $table->text('ticket_message')->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('ticket_number_prefix', 20)->default('TKT');
            $table->unsignedSmallInteger('ticket_number_padding')->default(6);
            $table->unsignedInteger('stock_alert_threshold')->default(5);
            $table->decimal('max_discount_percent', 5, 2)->default(10.00);
            // Annulations > ce montant nécessitent validation superviseur
            $table->decimal('supervisor_approval_threshold', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
