<?php

namespace Modules\Settings\app\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Settings\app\Http\Livewire\SettingsForm;

class SettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'settings');

        if (file_exists(__DIR__ . '/../../routes/web.php')) {
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(__DIR__ . '/../../routes/web.php');
        }

        Livewire::component('settings.settings-form', SettingsForm::class);
    }
}
