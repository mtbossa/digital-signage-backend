<?php

namespace Tests\Feature\Post\Traits;

use App\Models\Post;

trait PostTestsTrait
{
  private Post $post;

  private function _makePost(array $data = null): Post
  {
    return Post::factory()->make($data);
  }

  private function _createPost(array $data = null): Post
  {
    return Post::factory()->create($data);
  }
}
