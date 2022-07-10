<?php

namespace App\Providers;

use App\Events\Post\ShouldEndPost;
use App\Events\Post\ShouldStartPost;
use App\Listeners\Post\BroadcastToRaspberries;
use App\Listeners\Post\SchedulePostEnd;
use App\Listeners\Post\SchedulePostStart;
use App\Listeners\Post\SetShowingFalse;
use App\Listeners\Post\SetShowingTrue;
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
                BroadcastToRaspberries::class,
                SchedulePostStart::class,
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
