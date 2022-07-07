<?php

namespace App\Http\Controllers;

use App\Actions\Post\StorePostAction;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Post;
use App\Services\PostStartAndEndDispatcher;
use Illuminate\Database\Eloquent\Collection;

class PostController extends Controller
{
  public function index(): Collection
  {
    return Post::all();
  }

  public function store(StorePostRequest $request, StorePostAction $action, PostStartAndEndDispatcher $service): Post
  {
    return $action->handle($request, $service);
  }

  public function show(Post $post): Post
  {
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
