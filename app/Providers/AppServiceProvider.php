<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View; // [BARU] Import View facade
use App\Http\View\Composers\SettingsComposer; // [BARU] Import SettingsComposer

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
        Schema::defaultStringLength(191);

        // [BARU] Mendaftarkan SettingsComposer ke view yang spesifik
        View::composer(
            [
                'sales.print-pdf', 
                'sales.print-thermal', 
                'reports.pdf.*' // Ini akan berlaku untuk semua view di dalam folder reports/pdf
            ], 
            SettingsComposer::class
        );
    }
}