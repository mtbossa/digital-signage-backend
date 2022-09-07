<?php

namespace App\Listeners\Post;

use App\Notifications\DisplayPost\PostDeleted;

class BroadcastToPostDisplays
{
  public function __construct()
  {
    //
  }

  public function handle(\App\Events\Post\PostDeleted $event): void
  {
    foreach ($event->post->displays as $display) {
      $notification = new PostDeleted($event->post,
        $display);

      $display->notify($notification);
    }
  }
}
