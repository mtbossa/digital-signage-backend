<?php

namespace App\Events\Post;

use App\Models\Post;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShouldStartPost
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Post $post)
    {
    }
}
