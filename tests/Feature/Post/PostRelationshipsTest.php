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

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
  }

  /** @test */
  public function check_if_post_belongs_to_a_media_relationship_is_working()
  {
    $media = Media::factory()->create();
    $post = Post::factory()->create(['media_id' => $media->id]);

    $this->assertEquals(1, $post->media->count());
    $this->assertInstanceOf(Media::class, $post->media);
    $this->assertDatabaseHas('posts', ['media_id' => $media->id]);
  }

  /** @test */
  public function should_link_media_to_post_when_creating_post_with_media()
  {
    $media = Media::factory()->create();
    $post_data = Post::factory()->nonRecurrent()->make()->toArray();

    $this->postJson(route('posts.store'),
      [...$post_data, 'media_id' => $media->id])->assertCreated()->assertJson(['media_id' => $media->id]);

    $this->assertDatabaseHas('posts', ['media_id' => $media->id]);
  }

  /** @test */
  public function should_link_recurrence_to_post_when_creating_post_with_recurrence()
  {
    $this->withoutExceptionHandling();
    $media = Media::factory()->create();
    $post_data = Post::factory()->make()->toArray();
    $recurrence = Recurrence::factory()->create();

    $response = $this->postJson(route('posts.store'),
      [
        ...$post_data, 'media_id' => $media->id, 'recurrence_id' => $recurrence->id
      ])->assertCreated()->assertJson(['recurrence_id' => $recurrence->id]);

    $this->assertDatabaseHas('posts', ['id' => $response['id'], 'recurrence_id' => $recurrence->id]);
  }

  /** @test */
  public function post_should_be_deleted_when_media_is_deleted()
  {
    $media = Media::factory()->create();
    $post = Post::factory()->create(['media_id' => $media->id]);

    $this->deleteJson(route('medias.destroy', $media->id));

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
    $non_recurrent_post = Post::factory()->nonRecurrent()->create(['media_id' => $media->id]);
    $post = Post::factory()->create(['media_id' => $media->id, 'recurrence_id' => $recurrence->id]);

    $this->deleteJson(route('recurrences.destroy', $recurrence->id));

    $this->assertModelMissing($recurrence);
    $this->assertModelMissing($post);
    $this->assertModelExists($non_recurrent_post);
  }
}
