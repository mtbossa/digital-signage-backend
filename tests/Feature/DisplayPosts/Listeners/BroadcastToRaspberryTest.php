<?php


namespace DisplayPosts\Listeners;

use App\Events\DisplayPost\DisplayPostCreated;
use App\Events\DisplayPost\DisplayPostDeleted;
use App\Models\Display;
use App\Models\Post;
use App\Models\Raspberry;
use App\Notifications\DisplayPost\PostCreated;
use App\Notifications\DisplayPost\PostDeleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class BroadcastToRaspberryTest extends TestCase
{
  use RefreshDatabase, PostTestsTrait, AuthUserTrait;

  public function setUp(): void
  {
    parent::setUp();

    $this->_authUser();
    $this->media = $this->_createMedia();
    $this->post = Post::factory()->nonRecurrent()
      ->create(['media_id' => $this->media->id]);
  }

  /**
   * @test
   */
  public function when_event_is_DisplayPostCreated_should_notify_raspberry_with_PostCreated_notification()
  {
    Notification::fake([PostCreated::class]);

    $displaysWithThisPost = Display::factory(3)->create();
    $displaysNoPost = Display::factory(2)->create();

    foreach (
      $displaysWithThisPost as $display
    ) {
      $raspberry = Raspberry::factory()->create(["display_id" => $display->id]);
      $this->post->displays()->attach($display->id);

      event(new DisplayPostCreated($display, $this->post));

      Notification::assertSentTo(
        $raspberry,
        PostCreated::class
      );
    }
  }

  /**
   * @test
   */
  public function when_event_is_DisplayPostDeleted_should_notify_raspberry_with_PostDeleted_notification()
  {
    Notification::fake([PostDeleted::class]);

    $displaysNoPost = Display::factory(2)->create();
    $displaysWithThisPost = Display::factory(3)->create();
    $displaysWithPostIds = $displaysWithThisPost->pluck('id')->toArray();

    $newDisplaysPost = Display::factory(4)->create();
    $newDisplaysPostIds = $newDisplaysPost->pluck('id')->toArray();

    $this->post->displays()->attach($displaysWithPostIds);

    $this->post->displays()->sync($newDisplaysPostIds);

    foreach ($displaysWithThisPost as $removedDisplay) {
      $raspberry = Raspberry::factory()->create(["display_id" => $removedDisplay->id]);
      event(new DisplayPostDeleted($removedDisplay, $this->post));
      Notification::assertSentTo(
        $raspberry,
        PostDeleted::class
      );
    }
  }
}
