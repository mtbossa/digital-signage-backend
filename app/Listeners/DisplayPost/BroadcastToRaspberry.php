<?php

namespace App\Listeners\DisplayPost;

use App\Events\DisplayPost\DisplayPostCreated;
use App\Events\DisplayPost\DisplayPostDeleted;
use App\Notifications\DisplayPost\PostCreated;
use App\Notifications\DisplayPost\PostDeleted;

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
  public function handle(DisplayPostCreated|DisplayPostDeleted $event): void
  {
    $display = $event->display;
    $post = $event->post;

    if ($event instanceof DisplayPostCreated) {
      $notification = new PostCreated($post,
        $display);
    } else {
      $notification = new PostDeleted($display, $post->id, $post->media->id);
    }

    if ($display->raspberry) {
      $display->raspberry->notify($notification);
    }
  }
}