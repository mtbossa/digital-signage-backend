<?php

namespace App\Listeners\Post;

use App\Events\Post\ShouldEndPost;
use App\Events\Post\ShouldStartPost;
use App\Notifications\Post\PostEnded;
use App\Notifications\Post\PostStarted;

class BroadcastToDisplays
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
            if ($event instanceof ShouldStartPost) {
                $notification = new PostStarted($event->post,
                    $display);
            } else {
                $notification = new PostEnded($event->post,
                    $display);
            }

            $display->notify($notification);
        }
    }
}
