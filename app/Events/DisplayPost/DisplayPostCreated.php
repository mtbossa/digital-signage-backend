<?php

namespace App\Events\DisplayPost;

use App\Models\Display;
use App\Models\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DisplayPostCreated
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(public Display $display, public Post $post)
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
  }
}
