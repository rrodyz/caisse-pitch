<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            // Le produit composé que cette recette produit (ex: "Mojito")
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('description')->nullable();
            // Coût calculé = somme(prix_achat_ingrédient × quantité)
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('margin', 10, 2)->default(0);
            $table->decimal('margin_rate', 8, 2)->default(0);
            $table->decimal('markup_rate', 8, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 10, 4)->default(1);
            $table->timestamps();

            $table->unique(['recipe_id', 'product_id']);
            $table->index('recipe_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
        Schema::dropIfExists('recipes');
    }
};
