<?php

namespace App\Support;

use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitGuard
{
    /**
     * Applique un rate limiter nommé (défini dans AppServiceProvider).
     *
     * @param  callable(int): void|null  $onLimited  Callback avec secondes restantes (Livewire)
     *
     * @throws ThrottleRequestsException
     */
    public static function enforce(string $name, ?callable $onLimited = null, ?Request $request = null): bool
    {
        $request = $request ?? request();
        $limiter = RateLimiter::limiter($name);

        if ($limiter === null) {
            return true;
        }

        $limit = $limiter($request);
        $key = md5($name.$limit->key);

        if (RateLimiter::tooManyAttempts($key, $limit->maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            if ($onLimited !== null) {
                $onLimited($seconds);

                return false;
            }

            throw new ThrottleRequestsException(
                message: 'Too Many Attempts.',
                code: 0,
                headers: [
                    'Retry-After' => $seconds,
                    'X-RateLimit-Limit' => $limit->maxAttempts,
                    'X-RateLimit-Remaining' => 0,
                ],
            );
        }

        RateLimiter::hit($key, $limit->decaySeconds);

        return true;
    }
}
