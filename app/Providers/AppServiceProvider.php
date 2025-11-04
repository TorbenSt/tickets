<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Spezieller Rate Limiter für iFrame Auth
        RateLimiter::for('iframe-auth', function (Request $request) {
            return [
                Limit::perMinute(10)->by($request->ip()), // 10 pro Minute pro IP
                Limit::perHour(50)->by($request->ip()),   // 50 pro Stunde pro IP
            ];
        });
    }
}
