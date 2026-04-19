<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('auth-login', function (Request $request): array {
            return [
                Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()),
            ];
        });

        RateLimiter::for('auth-register', function (Request $request): array {
            return [
                Limit::perMinutes(15, 3)->by(strtolower((string) $request->input('email')).'|'.$request->ip()),
            ];
        });

        RateLimiter::for('auth-verify', function (Request $request): array {
            return [
                Limit::perMinutes(10, 6)->by(strtolower((string) $request->input('email')).'|'.$request->ip()),
            ];
        });

        RateLimiter::for('auth-resend', function (Request $request): array {
            return [
                Limit::perMinutes(10, 3)->by(strtolower((string) $request->input('email')).'|'.$request->ip()),
            ];
        });

        RateLimiter::for('auth-password-email', function (Request $request): array {
            return [
                Limit::perMinutes(15, 3)->by(strtolower((string) $request->input('email')).'|'.$request->ip()),
            ];
        });

        RateLimiter::for('auth-password-reset', function (Request $request): array {
            return [
                Limit::perMinutes(10, 5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()),
            ];
        });

        RateLimiter::for('repair-requests', function (Request $request): array {
            return [
                Limit::perMinutes(15, 5)->by($request->ip()),
            ];
        });

        if (config('communications.domain.force_https')) {
            URL::forceScheme('https');
        }

        $canonicalHost = config('communications.domain.canonical_host');

        if ($canonicalHost) {
            $scheme = config('communications.domain.force_https')
                ? 'https'
                : (parse_url((string) config('communications.domain.app_url'), PHP_URL_SCHEME) ?: 'http');

            URL::forceRootUrl("{$scheme}://{$canonicalHost}");
        }
    }
}
