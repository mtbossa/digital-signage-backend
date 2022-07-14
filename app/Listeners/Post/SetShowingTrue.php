<?php

namespace App\Listeners\Post;

use App\Events\Post\ShouldStartPost;

class SetShowingTrue
{
    public function handle(ShouldStartPost $event): void
    {
        $event->post->showing = true;
        $event->post->save();
    }
}
