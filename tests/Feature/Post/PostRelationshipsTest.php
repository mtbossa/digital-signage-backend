<?php

namespace Tests\Feature\Post;

use App\Models\Media;
use App\Models\Post;
use App\Models\Recurrence;
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

  /** @test */
  public function a_post_might_belong_to_a_recurrence()
  {
    $media = Media::factory()->create();
    $recurrence = Recurrence::factory()->create();
    $post = Post::factory()->create(['media_id' => $media->id, 'recurrence_id' => $recurrence->id]);

    $this->assertEquals(1, $post->recurrence->count());
    $this->assertInstanceOf(Recurrence::class, $post->recurrence);
    $this->assertDatabaseHas('posts', ['id' => $post->id, 'recurrence_id' => $recurrence->id]);
  }

  /** @test */
  public function post_should_be_deleted_when_recurrence_is_deleted()
  {
    $media = Media::factory()->create();
    $recurrence = Recurrence::factory()->create();
    $post_without_recurrence = Post::factory()->create(['media_id' => $media->id]);
    $post = Post::factory()->recurrent()->create(['media_id' => $media->id, 'recurrence_id' => $recurrence->id]);

    $recurrence->delete();

    $this->assertModelMissing($recurrence);
    $this->assertModelMissing($post);
    $this->assertModelExists($post_without_recurrence);
  }

  /** @test */
  public function should_create_post_with_recurrence()
  {
    $media = Media::factory()->create();
    $recurrence = Recurrence::factory()->create();
    $post = Post::factory()->recurrent()->create(['media_id' => $media->id, 'recurrence_id' => $recurrence->id]);

    $this->assertModelExists($post);
    $this->assertNull($post->start_date);
    $this->assertNull($post->end_date);
  }


}
