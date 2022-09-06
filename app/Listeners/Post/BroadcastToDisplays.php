<?php

namespace App\Listeners\Post;

use App\Events\Post\PostMustEnd;
use App\Events\Post\PostMustStart;
use App\Notifications\Post\PostEnded;
use App\Notifications\Post\PostStarted;

class BroadcastToDisplays
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
   * @param  object  $event
   *
   * @return void
   */
  public function handle(PostMustStart|PostMustEnd $event): void
  {
    foreach ($event->post->displays as $display) {
      if ($event instanceof PostMustStart) {
        $notification = new PostStarted($event->post,
          $display);
      } else {
        $notification = new PostEnded($event->post,
          $display);
      }

      $display->notify($notification);
    }
  }
}
