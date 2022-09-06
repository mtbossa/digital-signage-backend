<?php

namespace App\Listeners\Post;

use App\Events\Post\PostMustStart;

class SetShowingTrue
{
  public function handle(PostMustStart $event): void
  {
    $event->post->showing = true;
    $event->post->save();
  }
}
