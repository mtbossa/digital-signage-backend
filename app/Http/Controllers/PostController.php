<?php

namespace App\Http\Controllers;

use App\Actions\Post\StorePostAction;
use App\Http\Requests\Post\StorePostRequest;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class PostController extends Controller
{
  public function index(): Collection
  {
    return Post::all();
  }

  public function store(StorePostRequest $request, StorePostAction $action): Post
  {
    return $action->handle($request);
  }

  public function show(Post $post): Post
  {
    return $post;
  }

  public function update(Request $request, Post $post): Post
  {
    $post->update($request->all());

    return $post;
  }

  public function destroy(Post $post): bool
  {
    return $post->delete();
  }
}
