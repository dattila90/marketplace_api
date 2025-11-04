<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // General application bindings (non-domain specific)

        // Product-related bindings are now handled by ProductServiceProvider
        // for better separation of concerns
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // General application bootstrapping
    }
}
