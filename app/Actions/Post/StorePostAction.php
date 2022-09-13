<?php

namespace App\Actions\Post;

use App\Events\DisplayPost\DisplayPostCreated;
use App\Jobs\ExpirePost;
use App\Models\Display;
use App\Models\Media;
use App\Models\Post;
use App\Models\Recurrence;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StorePostAction
{
  public function handle(
    Request $request,
  ): Post {
    $media = Media::findOrFail($request->media_id);
    $post = $media->posts()->create($request->except(['media_id']));

    if ($request->has('recurrence_id')) {
      $recurrence = Recurrence::findOrFail($request->recurrence_id);
      $post->recurrence()->associate($recurrence);
      $post->save();
    } else {
      $this->schedulePostExpiredEvent($post);
    }

    if (!is_null($request->displays_ids)) {
      $displays_ids = $request->displays_ids;
      $post->displays()->attach($displays_ids);

      foreach ($displays_ids as $display_id) {
        $display = Display::query()->find($display_id);
        DisplayPostCreated::dispatch($display, $post);
      }

      $post->load('displays');
    }

    return $post;
  }

  private function schedulePostExpiredEvent(Post $post)
  {
    $end_date = $post->end_date;
    $end_time = $post->end_time;
    $end = Carbon::createFromFormat('Y-m-d H:i:s', "$end_date $end_time");
    $delay = $end->diffInSeconds(now());
    ExpirePost::dispatch($post)->delay($delay);
  }
}
