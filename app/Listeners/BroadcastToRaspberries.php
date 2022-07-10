<?php

namespace App\Listeners;

use App\Events\ShouldEndPost;
use App\Events\ShouldStartPost;
use App\Notifications\PostEnded;
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
    public function handle(ShouldStartPost|ShouldEndPost $event): void
    {
        foreach ($event->post->displays as $display) {
            if ($display->raspberry) {
                if ($event instanceof ShouldStartPost) {
                    $notification = new PostStarted($event->post,
                        $display);
                } else {
                    $notification = new PostEnded($event->post,
                        $display);
                }

                $display->raspberry->notify($notification);
            }
        }
    }
}
