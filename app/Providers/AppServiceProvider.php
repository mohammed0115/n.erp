<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use DB;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot()
    {
        // if( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
        //     URL::forceScheme('https');
        // }
        Schema::defaultStringLength(191);

        if ($this->app->runningInConsole()) {
            return;
        }
        //setting language
        if(isset($_COOKIE['language'])) {
            \App::setLocale($_COOKIE['language']);
        } else {
            \App::setLocale('en');
        }
        //get general setting value        
        try {
            if (! Schema::hasTable('general_settings')) {
                View::share('general_setting', null);
                View::share('currency', null);
                View::share('alert_product', 0);
                return;
            }

            $general_setting = DB::table('general_settings')->latest()->first();

            if (! $general_setting) {
                View::share('general_setting', null);
                View::share('currency', null);
                View::share('alert_product', 0);
                return;
            }

            $currency = null;
            if (Schema::hasTable('currencies') && ! empty($general_setting->currency)) {
                $currency = \App\Currency::find($general_setting->currency);
            }

            View::share('general_setting', $general_setting);
            View::share('currency', $currency);

            config([
                'staff_access' => $general_setting->staff_access ?? null,
                'date_format' => $general_setting->date_format ?? null,
                'currency' => $currency ? $currency->code : null,
                'currency_position' => $general_setting->currency_position ?? null,
            ]);

            $alert_product = 0;
            if (Schema::hasTable('products')) {
                $alert_product = DB::table('products')
                    ->where('is_active', true)
                    ->whereColumn('alert_quantity', '>', 'qty')
                    ->count();
            }

            View::share('alert_product', $alert_product);
        } catch (\Throwable $e) {
            View::share('general_setting', null);
            View::share('currency', null);
            View::share('alert_product', 0);
        }
    }
}
