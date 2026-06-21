<?php

namespace Modules\Purchases\app\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Purchases\app\Http\Livewire\PurchaseForm;
use Modules\Purchases\app\Http\Livewire\PurchaseList;

class PurchasesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'purchases');
        \Illuminate\Support\Facades\Route::middleware('web')
            ->group(__DIR__ . '/../../routes/web.php');

        Livewire::component('purchases.purchase-list', PurchaseList::class);
        Livewire::component('purchases.purchase-form', PurchaseForm::class);
    }
}
