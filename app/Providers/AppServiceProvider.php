<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

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
        // 1) Bahasa/locale tanggal: Indonesia
        Carbon::setLocale('id');

        // 2) (Opsional) Hindari error index-length di MySQL versi lama
        Schema::defaultStringLength(191);

        // 3) (Opsional) Paksa HTTPS di production jika APP_URL pakai https
        if (config('app.env') === 'production' && str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
