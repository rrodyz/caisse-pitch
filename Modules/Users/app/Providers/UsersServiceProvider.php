<?php

namespace Modules\Users\app\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Users\app\Http\Livewire\RoleManager;
use Modules\Users\app\Http\Livewire\UserManager;
use Modules\Users\app\Http\Livewire\ActivityLogViewer;

class UsersServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'users');
        \Illuminate\Support\Facades\Route::middleware('web')
            ->group(__DIR__ . '/../../routes/web.php');

        Livewire::component('users.user-manager', UserManager::class);
        Livewire::component('users.role-manager', RoleManager::class);
        Livewire::component('users.activity-log-viewer', ActivityLogViewer::class);
    }
}
