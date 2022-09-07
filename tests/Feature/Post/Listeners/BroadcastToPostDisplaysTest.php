<?php


namespace Post\Listeners;

use App\Models\Display;
use App\Models\Post;
use App\Notifications\DisplayPost\PostDeleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class BroadcastToPostDisplaysTest extends TestCase
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
  public function should_notify_all_displays_from_deleted_post_with_post_deleted_notification()
  {
    Notification::fake([PostDeleted::class]);

    $displaysNoPost = Display::factory(2)->create();
    $displaysWithThisPost = Display::factory(3)->create();
    $displaysWithThisPostIds = $displaysWithThisPost->pluck('id')->toArray();
    $this->post->displays()->sync($displaysWithThisPostIds);

    event(new \App\Events\Post\PostDeleted($this->post));

    foreach ($displaysWithThisPost as $display) {
      Notification::assertSentToTimes(
        $display,
        PostDeleted::class,
        1
      );
    }
  }
}
