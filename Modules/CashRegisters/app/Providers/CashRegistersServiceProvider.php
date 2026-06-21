<?php

namespace Modules\CashRegisters\app\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\CashRegisters\app\Http\Livewire\CashRegisterManager;
use Modules\CashRegisters\app\Http\Livewire\CashSessionManager;
use Modules\CashRegisters\app\Services\CashSessionService;

class CashRegistersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CashSessionService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'cashregisters');
        \Illuminate\Support\Facades\Route::middleware('web')
            ->group(__DIR__ . '/../../routes/web.php');

        Livewire::component('cashregisters.cash-register-manager', CashRegisterManager::class);
        Livewire::component('cashregisters.cash-session-manager', CashSessionManager::class);
    }
}
