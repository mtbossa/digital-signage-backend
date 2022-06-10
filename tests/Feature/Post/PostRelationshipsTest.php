<?php

namespace Tests\Feature\Post;

use App\Models\Display;
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
  public function check_if_post_belongs_to_many_displays_relationship_is_working()
  {
    $media = Media::factory()->create();
    $displays = Display::factory(2)->create();
    $post = Post::factory()->create(['media_id' => $media->id]);

    foreach ($displays as $display) {
      $post->displays()->attach($display->id);
    }

    $this->assertEquals(2, $post->displays()->count());
    $this->assertInstanceOf(Display::class, $post->displays[0]);

    foreach ($displays as $display) {
      $this->assertDatabaseHas('display_post', ['post_id' => $post->id, 'display_id' => $display->id]);
    }
  }

  /** @test */
  public function ensure_can_attach_displays_when_creating_post()
  {
    $media = Media::factory()->create();
    $displays_ids = Display::factory(2)->create()->pluck('id');
    $post_data = Post::factory()->nonRecurrent()->make(['media_id' => $media->id])->toArray();

    $response = $this->postJson(route('posts.store'), [...$post_data, 'displays_ids' => $displays_ids->toArray()])
      ->assertCreated();


    foreach ($displays_ids as $key => $id) {
      $response->assertJson(['displays' => [$key => ['id' => $id]]]);
      $this->assertDatabaseHas('display_post', ['post_id' => $response['id'], 'display_id' => $id]);
    }
  }

  /** @test */
  public function ensure_can_attach_displays_when_updating_post()
  {
    $media = Media::factory()->create();
    $displays_ids = Display::factory(2)->create()->pluck('id');
    $post = Post::factory()->nonRecurrent()->create(['media_id' => $media->id]);

    $response = $this->putJson(route('posts.update', $post->id), [...$post->toArray(), 'displays_ids' => $displays_ids->toArray()])
      ->assertOk();

    foreach ($displays_ids as $key => $id) {
      $response->assertJson(['displays' => [$key => ['id' => $id]]]);
      $this->assertDatabaseHas('display_post', ['post_id' => $response['id'], 'display_id' => $id]);
    }
  }

  /** @test */
  public function ensure_not_passed_displays_are_detached_when_updating_post()
  {
    $media = Media::factory()->create();
    $displays_ids = Display::factory(3)->create()->pluck('id')->toArray();
    $post = Post::factory()->nonRecurrent()->create(['media_id' => $media->id]);
    $post->displays()->attach($displays_ids);

    $removed_display_id = array_pop($displays_ids);

    $response = $this->putJson(route('posts.update', $post->id), [...$post->toArray(), 'displays_ids' => $displays_ids])
      ->assertOk();

    foreach ($displays_ids as $key => $id) {
      $response->assertJson(['displays' => [$key => ['id' => $id]]]);
      $this->assertDatabaseHas('display_post', ['post_id' => $response['id'], 'display_id' => $id]);
    }
    $this->assertDatabaseMissing('display_post', ['post_id' => $post->id, 'display_id' => $removed_display_id]);
  }

  /** @test */
  public function ensure_all_displays_are_detached_when_updating_post_if_displays_ids_is_null()
  {
    $media = Media::factory()->create();
    $displays_ids = Display::factory(3)->create()->pluck('id')->toArray();
    $post = Post::factory()->nonRecurrent()->create(['media_id' => $media->id]);
    $post->displays()->attach($displays_ids);

    $response = $this->putJson(route('posts.update', $post->id), [...$post->toArray(), 'displays_ids' => null])
      ->assertOk();

    foreach ($displays_ids as $display_id) {
      $this->assertDatabaseMissing('display_post', ['post_id' => $post->id, 'display_id' => $display_id]);
    }
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
      [...$post_data, 'media_id' => $media->id, 'displays_ids' => null])->assertCreated()->assertJson(['media_id' => $media->id]);

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
        ...$post_data, 'media_id' => $media->id, 'recurrence_id' => $recurrence->id, 'displays_ids' => null
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
