<?php

namespace App\Listeners\Post;

use App\Events\Post\ShouldEndPost;

class SetShowingFalse
{
    public function handle(ShouldEndPost $event): void
    {
        $event->post->showing = false;
        $event->post->save();
    }
}
