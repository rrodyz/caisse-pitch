<?php

namespace Modules\Stock\app\Providers;

use App\Events\PurchaseValidated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Stock\app\Http\Livewire\StockMovements;
use Modules\Stock\app\Listeners\HandlePurchaseValidated;
use Modules\Stock\app\Services\StockService;

class StockServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StockService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'stock');
        \Illuminate\Support\Facades\Route::middleware('web')
            ->group(__DIR__ . '/../../routes/web.php');

        Event::listen(PurchaseValidated::class, HandlePurchaseValidated::class);

        Livewire::component('stock.stock-movements', StockMovements::class);
    }
}
