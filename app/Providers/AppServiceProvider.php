<?php

namespace App\Providers;

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
        // Railway (dan PaaS sejenis) men-terminate HTTPS di proxy lalu meneruskan
        // request ke container lewat HTTP biasa, jadi Laravel tidak tahu request
        // aslinya HTTPS dan generate URL asset/route dengan skema http:// —
        // browser lalu blokir sebagai mixed content. Paksa https di production.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
