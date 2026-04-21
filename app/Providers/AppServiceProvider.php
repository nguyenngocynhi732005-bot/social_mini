<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // 1. Thêm dòng này để dùng được class URL

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        $appUrl = (string) config('app.url', '');
        if (strpos($appUrl, 'https://') === 0) {
            URL::forceScheme('https');
        }
    }
}