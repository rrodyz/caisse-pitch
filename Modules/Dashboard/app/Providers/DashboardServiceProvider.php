<?php

namespace Modules\Dashboard\app\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Dashboard\app\Http\Livewire\DashboardWidget;

class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'dashboard');

        Livewire::component('dashboard.dashboard-widget', DashboardWidget::class);
    }
}
