<?php

namespace App\Providers;

use App\Events\DisplayPost\DisplayPostCreated;
use App\Events\DisplayPost\DisplayPostDeleted;
use App\Listeners\DisplayPost\BroadcastToRaspberry;
use App\Models\Media;
use App\Observers\MediaObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class   EventServiceProvider extends ServiceProvider
{
  /**
   * The event listener mappings for the application.
   *
   * @var array<class-string, array<int, class-string>>
   */
  protected $listen
    = [
      Registered::class => [
        SendEmailVerificationNotification::class,
      ],
      DisplayPostCreated::class => [
        BroadcastToRaspberry::class,
      ],
      DisplayPostDeleted::class => [
        BroadcastToRaspberry::class,
      ],
    ];

  /**
   * The model observers for your application.
   *
   * @var array
   */
  protected $observers = [
    Media::class => [MediaObserver::class],
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
