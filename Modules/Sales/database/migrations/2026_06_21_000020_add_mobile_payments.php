<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE sales MODIFY payment_mode
                ENUM('cash','card','mobile_money','orange_money','moov_money','wave','credit')
                NOT NULL DEFAULT 'cash'");

            DB::statement("ALTER TABLE sales MODIFY payment_status
                ENUM('paid','partial','pending','failed')
                NOT NULL DEFAULT 'paid'");
        }

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('provider');                 // wave | orange | moov
            $table->string('status')->default('pending'); // pending|succeeded|failed|cancelled|expired
            $table->decimal('amount', 12, 2);
            $table->string('currency', 8)->default('XOF');
            $table->string('client_reference')->nullable(); // notre n° de vente
            $table->string('external_id')->nullable();       // id session Wave (cos-...)
            $table->text('checkout_url')->nullable();        // wave_launch_url
            $table->string('customer_phone')->nullable();
            $table->json('payload')->nullable();             // dernière réponse provider
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['provider', 'external_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE sales MODIFY payment_status
                ENUM('paid','partial','pending') NOT NULL DEFAULT 'paid'");
            DB::statement("ALTER TABLE sales MODIFY payment_mode
                ENUM('cash','card','mobile_money','credit') NOT NULL DEFAULT 'cash'");
        }
    }
};
