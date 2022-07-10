<?php

namespace App\Events\Post;

use App\Models\Display;
use App\Models\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostExpired
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Post $post, public Display $display)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        if (!$this->display->raspberry) {
            return null;
        }
        $raspberryId = $this->display->raspberry->id;
        return new PrivateChannel("raspberry.$raspberryId");
    }
}
