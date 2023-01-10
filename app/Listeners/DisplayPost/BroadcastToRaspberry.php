<?php

namespace App\Listeners\DisplayPost;

use App\Events\DisplayPost\DisplayPostCreated;
use App\Events\DisplayPost\DisplayPostDeleted;
use App\Events\DisplayPost\DisplayPostUpdated;
use App\Notifications\DisplayPost\PostCreated;
use App\Notifications\DisplayPost\PostDeleted;
use App\Notifications\DisplayPost\PostUpdated;

class BroadcastToRaspberry
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
  public function handle(DisplayPostCreated|DisplayPostDeleted|DisplayPostUpdated $event): void
  {
    $display = $event->display;
    $post = $event->post;

    switch ($event) {
      case $event instanceof DisplayPostCreated:
        $notification = new PostCreated($post,
          $display);
        break;
      case $event instanceof DisplayPostUpdated:
        $notification = new PostUpdated($post,
          $display);
        break;
      case $event instanceof DisplayPostDeleted:
        $notification = new PostDeleted($display, $post->id, $post->media->id);
        break;
    }

    if ($display->raspberry) {
      $display->raspberry->notify($notification);
    } else {
        $display->notify($notification);
    }
  }
}
