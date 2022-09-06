<?php

namespace App\Http\Controllers;

use App\Actions\Post\StorePostAction;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Display;
use App\Models\Post;
use App\Services\PostDispatcherService;
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
    PostDispatcherService $service
  ): Post {
    return $action->handle($request, $service);
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
      $post->displays()->sync($request->displays_ids);
      $post['displays'] = $post->displays->toArray();
    }

        return $post;
    }

    public function destroy(Post $post): bool
    {
        return $post->delete();
    }
}
