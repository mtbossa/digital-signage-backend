<?php

namespace App\Http\Controllers;

use App\Actions\Post\StorePostAction;
use App\Events\DisplayPost\DisplayPostCreated;
use App\Events\DisplayPost\DisplayPostDeleted;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Display;
use App\Models\Post;
use App\Notifications\DisplayPost\PostDeleted;
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

  public function update(UpdatePostRequest $request, Post $post): Post
  {
    $post->update($request->validated());

    if ($request->has('displays_ids')) {
      $result = $post->displays()->sync($request->displays_ids);

      foreach ($result['detached'] as $removedDisplayId) {
        $display = Display::query()->find($removedDisplayId);
        DisplayPostDeleted::dispatch($display, $post);
      }

      foreach ($result['attached'] as $newDisplayId) {
        $display = Display::query()->find($newDisplayId);
        DisplayPostCreated::dispatch($display, $post);
      }

      $post['displays'] = $post->displays->toArray();
    }

    return $post;
  }

  public function destroy(Post $post)
  {
    $post->load("displays");

    foreach ($post->displays as $display) {
      $notification = new PostDeleted($display, $post->id, $post->media->id);

      $display->notify($notification);
    }
    return $post->delete();
  }
}
