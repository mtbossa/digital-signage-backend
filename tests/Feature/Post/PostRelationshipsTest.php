<?php

namespace Tests\Feature\Post;

use App\Models\Media;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class PostRelationshipsTest extends TestCase
{
  use RefreshDatabase, AuthUserTrait;

  /** @test */
  public function a_post_belongs_to_a_media()
  {
    $media = Media::factory()->create();
    $post = Post::factory()->create(['media_id' => $media->id]);

    $this->assertEquals(1, $post->media->count());
    $this->assertInstanceOf(Media::class, $post->media);
    $this->assertDatabaseHas('posts', ['media_id' => $media->id]);
  }

  /** @test */
  public function post_should_be_deleted_when_media_is_deleted()
  {
    $media = Media::factory()->create();
    $post = Post::factory()->create(['media_id' => $media->id]);

    $media->delete();
    $this->assertModelMissing($media);
    $this->assertModelMissing($post);
  }
}
