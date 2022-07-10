<?php

namespace App\Listeners;

use App\Events\ShouldEndPost;

class SetShowingFalse
{
    public function handle(ShouldEndPost $event): void
    {
        $event->post->showing = false;
        $event->post->save();
    }
}
