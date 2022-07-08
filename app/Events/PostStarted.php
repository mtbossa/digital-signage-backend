<?php

namespace App\Events;

use App\Models\Display;
use App\Models\Post;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostStarted implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public function __construct(public Post $post, public Display $display)
  {
  }

  public function broadcastOn(): PrivateChannel|null
  {
    if (!$this->display->raspberry) {
      return null;
    }
    $raspberryId = $this->display->raspberry->id;
    return new PrivateChannel("raspberry.$raspberryId");
  }
}
