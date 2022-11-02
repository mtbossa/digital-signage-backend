<?php

namespace App\Http\Controllers;

use App\Actions\Post\StorePostAction;
use App\Actions\Post\UpdatePostAction;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Jobs\ExpirePost;
use App\Models\Display;
use App\Models\Post;
use App\Notifications\DisplayPost\PostDeleted;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;

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

  public function update(UpdatePostRequest $request, Post $post, UpdatePostAction $action): Post
  {
    return $action->handle($request, $post);
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
