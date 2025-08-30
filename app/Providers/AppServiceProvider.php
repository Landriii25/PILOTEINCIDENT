<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Pagination AdminLTE (Bootstrap 4)
        if (method_exists(Paginator::class, 'useBootstrapFour')) {
            Paginator::useBootstrapFour();
        } else {
            // fallback si jamais (Laravel >=9 supporte useBootstrapFive)
            Paginator::useBootstrap();
        }

        // Longueur par défaut (MySQL anciens)
        Schema::defaultStringLength(191);

        // Localisation Carbon (suivant ton config/app.php)
        Carbon::setLocale(config('app.locale', 'fr'));
        date_default_timezone_set(config('app.timezone', 'UTC'));
    }
}
