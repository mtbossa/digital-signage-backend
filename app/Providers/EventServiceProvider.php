<?php

namespace App\Providers;

use App\Events\ShouldEndPost;
use App\Events\ShouldStartPost;
use App\Listeners\BroadcastToRaspberries;
use App\Listeners\SchedulePostEnd;
use App\Listeners\SetShowingFalse;
use App\Listeners\SetShowingTrue;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen
        = [
            Registered::class      => [
                SendEmailVerificationNotification::class,
            ],
            ShouldStartPost::class => [
                SetShowingTrue::class,
                BroadcastToRaspberries::class,
                SchedulePostEnd::class,
            ],
            ShouldEndPost::class   => [
                SetShowingFalse::class,
            ]
        ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
