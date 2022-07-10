<?php

namespace App\Listeners;

use App\Events\PostStarted;
use App\Events\ShouldStartPost;

class BroadcastToRaspberries
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
    public function handle(ShouldStartPost $event): void
    {
        foreach ($event->post->displays as $display) {
            event(new PostStarted($event->post, $display));
        }
    }
}
