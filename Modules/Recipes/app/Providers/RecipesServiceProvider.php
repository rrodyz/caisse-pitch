<?php

namespace Modules\Recipes\app\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Recipes\app\Http\Livewire\RecipeManager;
use Modules\Recipes\app\Services\RecipeService;

class RecipesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RecipeService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'recipes');
        \Illuminate\Support\Facades\Route::middleware('web')
            ->group(__DIR__ . '/../../routes/web.php');

        Livewire::component('recipes.recipe-manager', RecipeManager::class);
    }
}
