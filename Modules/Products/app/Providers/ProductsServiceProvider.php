<?php

namespace Modules\Products\app\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Products\app\Http\Livewire\ProductList;

class ProductsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'products');
        \Illuminate\Support\Facades\Route::middleware('web')
            ->group(__DIR__ . '/../../routes/web.php');

        Livewire::component('products.product-list', ProductList::class);
    }
}
