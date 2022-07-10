<?php

namespace App\Listeners;

use App\Events\ShouldStartPost;

class SetShowingFalse
{
    public function handle(ShouldStartPost $event): void
    {
        $event->post->showing = false;
        $event->post->save();
    }
}
