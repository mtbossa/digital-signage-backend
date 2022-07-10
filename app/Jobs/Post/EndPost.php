<?php

namespace App\Jobs\Post;

use App\Events\Post\ShouldEndPost;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EndPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Post $post)
    {
    }

    public function handle(): void
    {
        event(new ShouldEndPost($this->post));
    }
}
