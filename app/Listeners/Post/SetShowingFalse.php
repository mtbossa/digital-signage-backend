<?php

namespace App\Listeners\Post;

use App\Events\Post\PostMustEnd;

class SetShowingFalse
{
  public function handle(PostMustEnd $event): void
  {
    $event->post->showing = false;
    $event->post->save();
  }
}
