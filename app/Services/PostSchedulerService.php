<?php

namespace App\Services;

use App\Jobs\ExpirePost;
use App\Models\Post;
use Carbon\Carbon;

class PostSchedulerService
{
  public static function schedulePostExpiredEvent(Post $post): void
  {
    $end_date = $post->end_date;
    $end_time = $post->end_time;
    $end = Carbon::createFromFormat('Y-m-d H:i:s', "$end_date $end_time");
    $delay = $end->diffInSeconds(now());
    ExpirePost::dispatch($post)->delay($delay);
  }
}
