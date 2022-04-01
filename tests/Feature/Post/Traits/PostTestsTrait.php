<?php

namespace Tests\Feature\Post\Traits;

use App\Models\Media;
use App\Models\Post;

trait PostTestsTrait
{
  private Post $post;
  private Media $media;

  private function _makePost(array $data = null): Post
  {
    return Post::factory()->make($data);
  }

  private function _createPost(array $data = null): Post
  {
    return Post::factory()->create($data);
  }

  private function _createMedia(array $data = null): Media
  {
    return Media::factory()->create($data);
  }
}
