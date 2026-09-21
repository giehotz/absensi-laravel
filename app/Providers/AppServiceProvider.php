<?php

namespace App\Providers;

use App\Models\AttendanceSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
        Carbon::setLocale(config('app.locale', 'id'));

        View::composer(['layouts.*', 'auth.*', 'welcome'], function ($view) {
            static $setting = false;
            if ($setting === false) {
                try {
                    $setting = Schema::hasTable('attendance_settings') ? AttendanceSetting::first() : null;
                } catch (\Throwable $e) {
                    $setting = null;
                }
            }
            $view->with('schoolSetting', $setting);
        });
    }
}
