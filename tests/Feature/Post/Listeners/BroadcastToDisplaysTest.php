<?php


namespace Post\Listeners;

use App\Events\Post\PostMustEnd;
use App\Events\Post\PostMustStart;
use App\Models\Display;
use App\Models\Post;
use App\Notifications\Post\PostEnded;
use App\Notifications\Post\PostStarted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class BroadcastToDisplaysTest extends TestCase
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
  public function when_event_is_PostMustStart_should_notify_all_displays_that_have_this_post_with_PostStarted_notification(
  )
  {
    Notification::fake();
    Bus::fake();
    Event::fakeExcept([PostMustStart::class]);


    $displaysWithThisPost = Display::factory(3)->create();
    $displaysNoPost = Display::factory(2)->create();

    foreach (
      $displaysWithThisPost as $display
    ) {
      $this->post->displays()->attach($display->id);
    }

    event(new PostMustStart($this->post));

    Notification::assertSentTo(
      $displaysWithThisPost,
      PostStarted::class
    );
  }

  /**
   * @test
   */
  public function when_event_is_PostMustEnd_should_notify_all_displays_that_have_this_post_with_PostEnded_notification()
  {
    Notification::fake();
    Bus::fake();
    Event::fakeExcept([PostMustEnd::class]);


    $displaysWithThisPost = Display::factory(3)->create();
    $displaysNoPost = Display::factory(2)->create();

    foreach (
      $displaysWithThisPost as $display
    ) {
      $this->post->displays()->attach($display->id);
    }

    event(new PostMustEnd($this->post));

    Notification::assertSentTo(
      $displaysWithThisPost,
      PostEnded::class
    );
  }

  /**
   * @test
   */
  public function when_event_is_PostMustStart_should_notify_all_displays_that_have_this_post_one_time_each_with_PostStarted_notification(
  )
  {
    Notification::fake();
    Bus::fake();
    Event::fakeExcept([PostMustStart::class]);

    $displaysWithThisPost = Display::factory(3)->create();
    $displaysNoPost = Display::factory(2)->create();

    foreach (
      $displaysWithThisPost as $display
    ) {
      $this->post->displays()->attach($display->id);
    }

    event(new PostMustStart($this->post));

    foreach ($displaysWithThisPost as $display) {
      Notification::assertSentToTimes(
        $display,
        PostStarted::class,
        1
      );
    }
  }

  /**
   * @test
   */
  public function when_event_is_PostMustEnd_should_notify_all_displays_that_have_this_post_one_time_each_with_PostEnded_notification(
  )
  {
    Notification::fake();
    Bus::fake();
    Event::fakeExcept([PostMustEnd::class]);

    $displaysWithThisPost = Display::factory(3)->create();
    $displaysNoPost = Display::factory(2)->create();

    foreach (
      $displaysWithThisPost as $display
    ) {
      $this->post->displays()->attach($display->id);
    }

    event(new PostMustEnd($this->post));

    foreach ($displaysWithThisPost as $display) {
      Notification::assertSentToTimes(
        $display,
        PostEnded::class,
        1
      );
    }
  }

  /**
   * @test
   */
  public function when_event_is_PostMustStart_should_not_notify_displays_that_dont_have_this_post_with_PostStarted_notification(
  )
  {
    Notification::fake();
    Bus::fake();
    Event::fakeExcept([PostMustStart::class]);

    $displaysWithThisPost = Display::factory(3)->create();
    $displaysNoPost = Display::factory(2)->create();

    $newPost = Post::factory()->create(['media_id' => $this->media->id]);
    $displayForNewPost = Display::factory()->create();
    $newPost->displays()->attach($displayForNewPost->id);

    $notNotifiableDisplays = [...$displaysNoPost, $displayForNewPost];

    foreach (
      $displaysWithThisPost as $display
    ) {
      $this->post->displays()->attach($display->id);
    }

    event(new PostMustStart($this->post));

    foreach ($notNotifiableDisplays as $display) {
      Notification::assertNotSentTo(
        $display,
        PostStarted::class
      );
    }
  }

  /**
   * @test
   */
  public function when_event_is_PostMustEnd_should_not_notify_displays_that_dont_have_this_post_with_PostEnded_notification(
  )
  {
    Notification::fake();
    Bus::fake();
    Event::fakeExcept([PostMustEnd::class]);

    $displaysWithThisPost = Display::factory(3)->create();
    $displaysNoPost = Display::factory(2)->create();

    $newPost = Post::factory()->create(['media_id' => $this->media->id]);
    $displayForNewPost = Display::factory()->create();
    $newPost->displays()->attach($displayForNewPost->id);

    $notNotifiableDisplays = [...$displaysNoPost, $displayForNewPost];

    foreach (
      $displaysWithThisPost as $display
    ) {
      $this->post->displays()->attach($display->id);
    }

    event(new PostMustEnd($this->post));

    foreach ($notNotifiableDisplays as $display) {
      Notification::assertNotSentTo(
        $display,
        PostEnded::class
      );
    }
  }
}
