<?php

namespace App\Listeners;

use App\Events\PostStarted;
use App\Jobs\EndPost;

class SchedulePostEnd
{
  /**
   * Create the event listener.
   *
   * @return void
   */
  public function __construct()
  {
    //
  }

  /**
   * Handle the event.
   *
   * @param  \App\Events\PostStarted  $event
   * @return void
   */
  public function handle(PostStarted $event)
  {
    EndPost::dispatch($event->post);
  }
}
