<?php

namespace App\Listeners;

use App\Events\ShouldStartPost;

class SetShowingTrue
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
        $event->post->showing = true;
        $event->post->save();
    }
}
