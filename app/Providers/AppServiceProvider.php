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

        View::composer(['layouts.*'], function ($view) {
            try {
                if (Schema::hasTable('attendance_settings')) {
                    $view->with('schoolSetting', AttendanceSetting::first());
                } else {
                    $view->with('schoolSetting', null);
                }
            } catch (\Throwable $e) {
                $view->with('schoolSetting', null);
            }
        });
    }
}
