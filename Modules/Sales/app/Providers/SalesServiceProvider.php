<?php

namespace Modules\Sales\app\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Sales\app\Http\Livewire\CustomerManager;
use Modules\Sales\app\Http\Livewire\PosTerminal;
use Modules\Sales\app\Http\Livewire\SaleList;
use Modules\Sales\app\Services\SaleService;
use Modules\Sales\app\Services\Payment\PaymentManager;
use Modules\Sales\app\Services\Payment\PaymentService;

class SalesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SaleService::class);
        $this->app->singleton(PaymentManager::class);
        $this->app->singleton(PaymentService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'sales');
        \Illuminate\Support\Facades\Route::middleware('web')
            ->group(__DIR__ . '/../../routes/web.php');

        Livewire::component('sales.pos-terminal', PosTerminal::class);
        Livewire::component('sales.sale-list', SaleList::class);
        Livewire::component('sales.customer-manager', CustomerManager::class);
    }
}
