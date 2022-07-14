<?php

namespace App\Providers;

use App\Interfaces\Implementations\RecurrScheduler;
use App\Interfaces\RecurrenceScheduler;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(RecurrenceScheduler::class,
            RecurrScheduler::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
