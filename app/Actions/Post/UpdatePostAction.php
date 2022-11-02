<?php

namespace App\Actions\Post;

use App\Events\DisplayPost\DisplayPostCreated;
use App\Events\DisplayPost\DisplayPostDeleted;
use App\Events\DisplayPost\DisplayPostUpdated;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Jobs\ExpirePost;
use App\Models\Display;
use App\Models\Post;
use App\Models\Recurrence;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UpdatePostAction
{
  public function handle(
    UpdatePostRequest $request,
    Post $post,
  ): Post {
    $post->update($request->validated());
    $post->load("displays");

    if ($this->isUpdatingNonRecurrentToRecurrent($request, $post)) {
      $recurrence = Recurrence::findOrFail($request->recurrence_id);

      $post->recurrence()->associate($recurrence);

      $post->start_date = null;
      $post->end_date = null;

      $post->save();
    }

    if ($this->isUpdatingRecurrentToNonRecurrent($request, $post)) {
      $post->recurrence()->disassociate();
      $post->save();

      $this->schedulePostExpiredEvent($post);
    }

    if ($request->has('displays_ids')) {
      $currentPostDisplaysIds = $post->displays->pluck("id")->toArray();
      $unchangedDisplaysIds = Collection::make($request->displays_ids)->filter(fn($id) => in_array($id,
        $currentPostDisplaysIds));

      $result = $post->displays()->sync($request->displays_ids);

      foreach ($result['detached'] as $removedDisplayId) {
        $display = Display::query()->find($removedDisplayId);
        DisplayPostDeleted::dispatch($display, $post);
      }

      foreach ($result['attached'] as $newDisplayId) {
        $display = Display::query()->find($newDisplayId);
        DisplayPostCreated::dispatch($display, $post);
      }

      foreach ($unchangedDisplaysIds as $unchangedDisplaysId) {
        $display = Display::query()->find($unchangedDisplaysId);
        DisplayPostUpdated::dispatch($display, $post);
      }

      $post->refresh();
    }

    return $post;
  }

  private function isUpdatingNonRecurrentToRecurrent(UpdatePostRequest $request, Post $post): bool
  {
    return $request->has('recurrence_id') && is_null($post->recurrence_id);
  }

  private function isUpdatingRecurrentToNonRecurrent(UpdatePostRequest $request, Post $post): bool
  {
    return $post->recurrence_id && $request->has('start_date') && $request->has('end_date');
  }

  private function schedulePostExpiredEvent(Post $post): void
  {
    $end_date = $post->end_date;
    $end_time = $post->end_time;
    $end = Carbon::createFromFormat('Y-m-d H:i:s', "$end_date $end_time");
    $delay = $end->diffInSeconds(now());
    ExpirePost::dispatch($post)->delay($delay);
  }
}
