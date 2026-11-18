<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

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
        // The application does not expose token-authenticated API routes. Keeping
        // Sanctum's vendor migration disabled also avoids a PostgreSQL 18 index
        // creation failure observed on Neon's pooled connection during startup.
        Sanctum::ignoreMigrations();
    }
}
