<?php

namespace Tests\Feature\Post;

use App\Events\DisplayPost\DisplayPostCreated;
use App\Events\DisplayPostDeleted;
use App\Models\Display;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class PostTest extends TestCase
{
  use RefreshDatabase, PostTestsTrait, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    Bus::fake();
    Event::fake();

    $this->_authUser();
    $this->media = $this->_createMedia();
    $this->recurrence = $this->_createRecurrence();
    $this->post = $this->_createPost([
      'media_id' => $this->media->id,
      'recurrence_id' => $this->recurrence->id
    ]);
  }

  /** @test */
  public function create_post()
  {
    $post_data = $this->_makePost(['media_id' => $this->media->id], false)
      ->toArray();

    $response = $this->postJson(route('posts.store'),
      [...$post_data, 'displays_ids' => null]);

    $this->assertDatabaseHas('posts', $post_data);

    $response->assertCreated()->assertJson($post_data);
  }

  /** @test */
  public function delete_post()
  {
    $response = $this->deleteJson(route('posts.destroy', $this->post->id));
    $this->assertDatabaseMissing('posts', ['id' => $this->post->id]);
    $response->assertOk();
  }

  /** @test */
  public function fetch_single_post()
  {
    $this->getJson(route('posts.show',
      $this->post->id))->assertOk()->assertJson($this->post->toArray());
  }

  /** @test */
  public function fetch_single_post_with_displays_ids()
  {
    $this->withoutExceptionHandling();
    $displays = Display::factory(5)->create();
    $displays_ids = $displays->pluck(['id'])->toArray();
    $this->post->displays()->attach($displays_ids);
    $this->getJson(route('posts.show', ['post' => $this->post->id, 'withDisplaysIds' => true],
    ))->assertOk()->assertJson([...$this->post->toArray(), 'displays_ids' => $displays_ids]);
  }

  /** @test */
  public function fetch_all_posts()
  {
    $this->_createPost(['media_id' => $this->media->id]);

    $this->getJson(route('posts.index'))->assertOk()->assertJsonCount(2,
      'data')->assertJsonFragment($this->post->toArray());
  }

  /** @test */
  public function ensure_only_description_is_updated_even_if_more_fields_are_sent()
  {
    $old_media_id = $this->post->media->id;
    $new_values = [
      'description' => Str::random(20), 'media_id' => 2,
      'recurrence_id' => 2
    ];

    $this->putJson(route('posts.update', $this->post->id),
      $new_values)->assertOk()->assertJson([
      'description' => $new_values['description'],
      'media_id' => $old_media_id
    ]);
  }

  /** @test */
  public function should_fire_display_post_created_event_after_creating_post_with_displays_ids_for_every_display_attached_to_post(
  )
  {
    $displaysAmount = 2;
    Event::fake(DisplayPostCreated::class);

    Display::factory()->create(); // Random Display
    $post = Post::factory()->nonRecurrent()->make(['media_id' => $this->media->id]);
    $displays_ids = Display::factory(2)->create()->pluck('id')->toArray();

    $response = $this->postJson(route('posts.store',
      [...$post->toArray(), 'displays_ids' => $displays_ids]))->assertCreated();
    Event::assertDispatchedTimes(DisplayPostCreated::class, $displaysAmount);
  }

  /** @test */
  public function should_fire_display_post_deleted_event_after_updating_post_with_displays_ids_for_every_display_detached(
  )
  {
    $displaysAmount = 2;
    Event::fake(DisplayPostDeleted::class);

    Display::factory()->create(); // Random Display
    $displays = Display::factory($displaysAmount)->create();
    $displays_ids = $displays->pluck('id')->toArray();
    $post = Post::factory()->nonRecurrent()->create(['media_id' => $this->media->id]);

    $post->displays()->attach($displays_ids);

    $this->patchJson(route('posts.update', $post->id),
      [...$post->toArray(), 'displays_ids' => []])->assertOk();
    Event::assertDispatchedTimes(DisplayPostDeleted::class, $displaysAmount);
  }
}
