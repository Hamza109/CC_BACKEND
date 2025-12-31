<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
        // Configure rate limiting
        RateLimiter::for('api', function (Request $request) {
            // General API rate limit: 60 requests per minute per IP
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('otp', function (Request $request) {
            // Stricter rate limit for OTP endpoints: 5 requests per minute per IP
            // This prevents brute force and SMS spam attacks
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('otp-verify', function (Request $request) {
            // Very strict rate limit for OTP verification: 10 requests per minute per IP
            // Prevents brute force attacks on OTP codes
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('chat', function (Request $request) {
            // Rate limit for chat endpoints: 30 requests per minute per IP
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('search', function (Request $request) {
            // Rate limit for search endpoints: 30 requests per minute per IP
            return Limit::perMinute(30)->by($request->ip());
        });
    }
}
