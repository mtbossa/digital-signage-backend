<?php

namespace Tests\Feature\Post\Traits;

use App\Models\Media;
use App\Models\Post;
use App\Models\Recurrence;

trait PostTestsTrait
{
  private Post $post;
  private Media $media;
  private Recurrence $recurrence;

  private function _makePost(array $data = null, bool $recurrent): Post
  {
    return $recurrent ? Post::factory()->make($data) : Post::factory()->nonRecurrent()->make($data);
  }

  private function _createPost(array $data = null): Post
  {
    return Post::factory()->create($data);
  }

  private function _createMedia(array $data = null): Media
  {
    return Media::factory()->create($data);
  }

  private function _createRecurrence(array $data = null): Recurrence
  {
    return Recurrence::factory()->create($data);
  }
}
