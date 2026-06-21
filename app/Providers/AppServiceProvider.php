<?php

namespace App\Providers;

use App\Listeners\UpdateLastLoginAt;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Règles de complexité appliquées à toute l'app (Password::defaults())
        Password::defaults(
            Password::min(10)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
        );

        Event::listen(Login::class, UpdateLastLoginAt::class);

        // Max 30 exports par utilisateur par minute (anti-scraping)
        RateLimiter::for('exports', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Max 10 ouvertures de session caisse par utilisateur par heure
        RateLimiter::for('cash-operations', function (Request $request) {
            return Limit::perHour(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}
