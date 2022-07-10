<?php

namespace App\Listeners;

use App\Events\ShouldStartPost;
use App\Notifications\PostStarted;

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
            if ($display->raspberry) {
                $display->raspberry->notify(new PostStarted($event->post,
                    $display));
            }
        }
    }
}
