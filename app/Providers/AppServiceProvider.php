<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    //public function register(): void
    //{
// Remove this duplicate boot method to avoid redeclaration error
   // App::setLocale(Session::get('locale', config('app.locale')));
//}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
