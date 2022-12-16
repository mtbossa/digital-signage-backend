<?php

namespace App\Actions\Post;

use App\Events\DisplayPost\DisplayPostCreated;
use App\Http\Requests\Post\StorePostRequest;
use App\Models\Display;
use App\Models\Media;
use App\Models\Post;
use App\Models\Recurrence;
use App\Services\DisplayUpdatesCache\DisplayUpdatesCacheKeysEnum;
use App\Services\DisplayUpdatesCache\DisplayUpdatesCacheService;
use App\Services\PostSchedulerService;

class StorePostAction
{
  public function __construct(private readonly DisplayUpdatesCacheService $display_updates_cache_service)
  {
  }

  public function handle(
    StorePostRequest $request,
  ): Post {
    $media = Media::findOrFail($request->media_id);
    $post = $media->posts()->create($request->safe()->except(['media_id']));

    if ($request->has('recurrence_id')) {
      $recurrence = Recurrence::findOrFail($request->recurrence_id);
      $post->recurrence()->associate($recurrence);
      $post->save();
    } else {
      PostSchedulerService::schedulePostExpiredEvent($post);
    }

    if (!is_null($request->displays_ids)) {
      $displays_ids = $request->displays_ids;
      $post->displays()->attach($displays_ids);

      foreach ($displays_ids as $display_id) {
        $display = Display::query()->find($display_id);
        DisplayPostCreated::dispatch($display, $post);
        
        $this->display_updates_cache_service->setCurrentCache(DisplayUpdatesCacheKeysEnum::PostCreated, $display_id, $post->id);
      }

      $post->load('displays');
    }

    return $post;
  }
}
