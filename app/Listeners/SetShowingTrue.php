<?php

namespace App\Listeners;

use App\Events\ShouldStartPost;

class SetShowingTrue
{
    public function handle(ShouldStartPost $event): void
    {
        $event->post->showing = true;
        $event->post->save();
    }
}
