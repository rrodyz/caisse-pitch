<?php

namespace Modules\Recipes\app\Services;

use Modules\Recipes\app\Models\Recipe;

class RecipeService
{
    /**
     * Vérifie si un produit est un produit composé (a une recette active).
     */
    public function isComposite(int $productId): bool
    {
        return Recipe::where('product_id', $productId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Retourne la recette active d'un produit, ou null.
     */
    public function getRecipeForProduct(int $productId): ?Recipe
    {
        return Recipe::with('ingredients.product')
            ->where('product_id', $productId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Retourne la liste des ingrédients à déduire du stock
     * pour vendre $quantity unités du produit composé.
     *
     * Retourne : [['product_id' => X, 'quantity' => Y], ...]
     * Utilisé par StockService (Étape 7) et SaleService (Étape 11).
     */
    public function getStockDeductions(int $productId, float $quantity = 1): array
    {
        $recipe = $this->getRecipeForProduct($productId);

        if (! $recipe) {
            // Pas de recette → déduction directe du produit lui-même
            return [['product_id' => $productId, 'quantity' => $quantity]];
        }

        return $recipe->ingredients->map(fn($i) => [
            'product_id' => $i->product_id,
            'quantity'   => round($i->quantity * $quantity, 4),
        ])->toArray();
    }

    /**
     * Coût de revient d'une recette pour X portions.
     */
    public function calculateCost(Recipe $recipe, float $portions = 1): float
    {
        $recipe->loadMissing('ingredients.product');

        return round(
            $recipe->ingredients->sum(fn($i) => ($i->product?->purchase_price ?? 0) * $i->quantity * $portions),
            2
        );
    }
}
