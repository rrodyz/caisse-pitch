<?php

namespace Modules\Reports\app\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Reports\app\Http\Livewire\SalesReport;
use Modules\Reports\app\Http\Livewire\StockReport;
use Modules\Reports\app\Http\Livewire\LossReport;
use Modules\Reports\app\Http\Livewire\PaymentReport;

class ReportsServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'reports');
        \Illuminate\Support\Facades\Route::middleware('web')
            ->group(__DIR__ . '/../../routes/web.php');

        Livewire::component('reports.sales-report', SalesReport::class);
        Livewire::component('reports.stock-report', StockReport::class);
        Livewire::component('reports.loss-report',    LossReport::class);
        Livewire::component('reports.payment-report', PaymentReport::class);
    }
}
