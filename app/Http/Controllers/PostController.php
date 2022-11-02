<?php

namespace App\Http\Controllers;

use App\Actions\Post\StorePostAction;
use App\Events\DisplayPost\DisplayPostCreated;
use App\Events\DisplayPost\DisplayPostDeleted;
use App\Events\DisplayPost\DisplayPostUpdated;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Jobs\ExpirePost;
use App\Models\Display;
use App\Models\Post;
use App\Models\Recurrence;
use App\Notifications\DisplayPost\PostDeleted;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PostController extends Controller
{
  public function index(Request $request): LengthAwarePaginator
  {
    return Post::query()->paginate($request->size);
  }

  public function store(
    StorePostRequest $request,
    StorePostAction $action,
  ): Post {
    return $action->handle($request);
  }

  public function show(Request $request, Post $post): Post
  {
    if ($request->withDisplaysIds) {
      $post->load([
        'displays' => function (BelongsToMany $query) {
          $query->select('displays.id');
        }
      ]);

      $post->displays_ids = $post->displays->map(function (Display $test) {
        return $test->id;
      })->toArray();

      unset($post->displays);
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

  private function isUpdatingNonRecurrentToRecurrent(UpdatePostRequest $request, Post $post)
  {
    return $request->has('recurrence_id') && is_null($post->recurrence_id);
  }

  private function isUpdatingRecurrentToNonRecurrent(UpdatePostRequest $request, Post $post)
  {
    return $post->recurrence_id && $request->has('start_date') && $request->has('end_date');
  }

  public function update(UpdatePostRequest $request, Post $post): Post
  {
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

  public function destroy(Post $post)
  {
    $post->load("displays");

    foreach ($post->displays as $display) {
      $notification = new PostDeleted($display, $post->id, $post->media->id);

      if ($display->raspberry) {
        $display->raspberry->notify($notification);
      }
    }
    return $post->delete();
  }
}
