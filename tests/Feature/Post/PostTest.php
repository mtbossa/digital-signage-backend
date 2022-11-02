<?php

namespace Tests\Feature\Post;

use App\Events\DisplayPost\DisplayPostCreated;
use App\Events\DisplayPost\DisplayPostDeleted;
use App\Events\DisplayPost\DisplayPostUpdated;
use App\Jobs\ExpirePost;
use App\Models\Display;
use App\Models\Media;
use App\Models\Post;
use App\Models\Raspberry;
use App\Models\Recurrence;
use App\Notifications\DisplayPost\PostDeleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
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
  public function update_post()
  {
    $post = Post::factory()->nonRecurrent()->create(['media_id' => $this->media->id]);

    $recurrence = Recurrence::factory()->create();
    $newPostData = Post::factory()->make(['media_id' => $this->media->id]);

    $this->patchJson(route('posts.update', ["post" => $post->id]),
      [...$newPostData->toArray(), 'displays_ids' => [], 'recurrence_id' => $recurrence->id])->assertOk();
  }

  /** @test */
  public function ensure_media_doesnt_change_on_update()
  {
    $post = Post::factory()->nonRecurrent()->create(['media_id' => $this->media->id]);

    $newMedia = Media::factory()->create();
    $newPostData = Post::factory()->nonRecurrent()->make(['media_id' => $newMedia->id]);

    $this->patchJson(route('posts.update', ["post" => $post->id]),
      [...$newPostData->toArray(), 'displays_ids' => []])->assertOk();
    $this->assertDatabaseHas("posts", ['id' => $post->id, 'media_id' => $this->media->id]);
  }

  /** @test */
  public function ensure_can_change_post_from_non_recurrent_to_recurrent()
  {
    $post = Post::factory()->nonRecurrent()->create(['media_id' => $this->media->id]);

    $recurrence = Recurrence::factory()->create();
    $newPostData = Post::factory()->make(['media_id' => $this->media->id]);

    $this->patchJson(route('posts.update', ["post" => $post->id]),
      [...$newPostData->toArray(), 'displays_ids' => [], 'recurrence_id' => $recurrence->id])->assertOk();

    $this->assertDatabaseHas("posts",
      ['id' => $post->id, 'recurrence_id' => $recurrence->id, 'start_date' => null, 'end_date' => null]);
  }

  /** @test */
  public function ensure_can_change_post_from_recurrent_to_non_recurrent()
  {
    $recurrence = Recurrence::factory()->create();
    $post = Post::factory()->create(['media_id' => $this->media->id, 'recurrence_id' => $recurrence->id]);

    $newPostData = Post::factory()->nonRecurrent()->make(['media_id' => $this->media->id]);

    $this->patchJson(route('posts.update', ["post" => $post->id]),
      [...$newPostData->toArray(), 'displays_ids' => []])->assertOk();

    $this->assertDatabaseHas("posts", [
      'id' => $post->id, 'recurrence_id' => null, 'start_date' => $newPostData->start_date,
      'end_date' => $newPostData->end_date
    ]);
  }

  /** @test */
  public function ensure_post_expired_event_is_scheduled_when_updataing_non_recurrent_to_recurrent()
  {
    Bus::fake([ExpirePost::class]);
    $recurrence = Recurrence::factory()->create();
    $post = Post::factory()->create(['media_id' => $this->media->id, 'recurrence_id' => $recurrence->id]);

    $newPostData = Post::factory()->nonRecurrent()->make(['media_id' => $this->media->id]);

    $this->patchJson(route('posts.update', ["post" => $post->id]),
      [...$newPostData->toArray(), 'displays_ids' => []])->assertOk();
    Bus::assertDispatchedTimes(ExpirePost::class, 1);
    Bus::assertDispatched(ExpirePost::class, function (ExpirePost $job) use ($post) {
      return $post->id === $job->post->id;
    });
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
    Event::assertDispatched(DisplayPostDeleted::class,
      fn(DisplayPostDeleted $event) => in_array($event->display->id, $displays_ids)
    );

  }

  /** @test */
  public function should_fire_display_post_created_event_after_updating_post_with_new_displays_ids_for_every_display_attached(
  )
  {
    $newDisplaysAmount = 4;
    Event::fake(DisplayPostCreated::class);

    Display::factory()->create(); // Random Display
    $post = Post::factory()->nonRecurrent()->create(['media_id' => $this->media->id]);
    $displays = Display::factory(2)->create();
    $displays_ids = $displays->pluck('id')->toArray();

    $post->displays()->attach($displays_ids);

    $newDisplays = Display::factory($newDisplaysAmount)->create();
    $new_displays_ids = $newDisplays->pluck('id')->toArray();


    $this->patchJson(route('posts.update', $post->id),
      [...$post->toArray(), 'displays_ids' => $new_displays_ids])->assertOk();
    Event::assertDispatchedTimes(DisplayPostCreated::class, $newDisplaysAmount);
    Event::assertDispatched(DisplayPostCreated::class,
      fn(DisplayPostCreated $event) => in_array($event->display->id, $new_displays_ids)
    );
  }

  /** @test */
  public function should_fire_display_post_updated_event_to_all_and_only_displays_that_were_already_linked_to_the_post()
  {
    $this->withoutExceptionHandling();
    Display::factory()->create(); // Random Display
    Event::fake(DisplayPostUpdated::class);

    $post = Post::factory()->nonRecurrent()->create(['media_id' => $this->media->id]);
    $removedDisplays = Display::factory(2)->create();
    $removed_displays_ids = $removedDisplays->pluck('id')->toArray();

    $remaningDisplaysAmount = 2;
    $remaningDisplays = Display::factory($remaningDisplaysAmount)->create();
    $remaningDisplaysIds = $remaningDisplays->pluck('id')->toArray();


    $post->displays()->attach([...$removed_displays_ids, ...$remaningDisplaysIds]);

    $newDisplaysAmount = 4;
    $newDisplays = Display::factory($newDisplaysAmount)->create();
    $new_displays_ids = $newDisplays->pluck('id')->toArray();


    $this->patchJson(route('posts.update', $post->id),
      [...$post->toArray(), 'displays_ids' => [...$new_displays_ids, ...$remaningDisplaysIds]])->assertOk();

    Event::assertDispatchedTimes(DisplayPostUpdated::class, $remaningDisplaysAmount);
    Event::assertDispatched(DisplayPostUpdated::class,
      fn(DisplayPostUpdated $event) => in_array($event->display->id, $remaningDisplaysIds)
    );
  }

  /** @test */
  public function when_post_is_deleted_should_dispatch_post_deleted_notification_for_each_raspberry()
  {
    Notification::fake(PostDeleted::class);

    $displays = Display::factory(2)->create();
    foreach ($displays as $display) {
      $raspberry = Raspberry::factory()->create((["display_id" => $display->id]));
    }
    $displaysIds = $displays->pluck('id')->toArray();
    $this->post->displays()->sync($displaysIds);
    $this->deleteJson(route('posts.destroy', $this->post->id))->assertOk();

    foreach ($displays as $display) {
      Notification::assertSentTo($display->raspberry, PostDeleted::class);
    }

  }
}
