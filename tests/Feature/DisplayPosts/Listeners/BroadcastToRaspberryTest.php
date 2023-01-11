<?php

namespace DisplayPosts\Listeners;

use App\Events\DisplayPost\DisplayPostCreated;
use App\Events\DisplayPost\DisplayPostDeleted;
use App\Events\DisplayPost\DisplayPostUpdated;
use App\Models\Display;
use App\Models\Post;
use App\Models\Raspberry;
use App\Notifications\DisplayPost\PostCreated;
use App\Notifications\DisplayPost\PostDeleted;
use App\Notifications\DisplayPost\PostUpdated;
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
        Notification::fake();

        $displaysWithThisPost = Display::factory(3)->create();
        $displaysNoPost = Display::factory(2)->create();

        foreach (
            $displaysWithThisPost as $display
        ) {
            $raspberry = Raspberry::factory()->create(['display_id' => $display->id]);
            $this->post->displays()->attach($display->id);

            event(new DisplayPostCreated($display, $this->post));

            Notification::assertSentTo(
                $raspberry,
                PostCreated::class
            );
        }

        Notification::assertTimesSent(count($displaysWithThisPost), PostCreated::class);
    }

      /**
       * @test
       */
      public function when_display_dont_have_raspberry_and_event_is_DisplayPostCreated_should_notify_display_with_PostCreated_notification()
      {
          Notification::fake();

          $displaysWithThisPost = Display::factory(3)->create();
          $displaysNoPost = Display::factory(2)->create();

          foreach (
              $displaysWithThisPost as $display
          ) {
              $this->post->displays()->attach($display->id);

              event(new DisplayPostCreated($display, $this->post));

              Notification::assertSentTo(
                  $display,
                  PostCreated::class
              );
          }

          Notification::assertTimesSent(count($displaysWithThisPost), PostCreated::class);
      }

      /**
       * @test
       */
      public function when_event_is_DisplayPostUpdated_should_notify_raspberry_with_PostUpdated_notification()
      {
          Notification::fake();

          $displaysWithThisPost = Display::factory(3)->create();
          $displaysNoPost = Display::factory(2)->create();

          foreach (
              $displaysWithThisPost as $display
          ) {
              $raspberry = Raspberry::factory()->create(['display_id' => $display->id]);
              $this->post->displays()->attach($display->id);

              event(new DisplayPostUpdated($display, $this->post));

              Notification::assertSentTo(
                  $raspberry,
                  PostUpdated::class
              );
          }
          Notification::assertTimesSent(count($displaysWithThisPost), PostUpdated::class);
      }

      /**
       * @test
       */
      public function when_display_dont_have_raspberry_and_event_is_DisplayPostUpdated_should_notify_display_with_PostUpdated_notification()
      {
          Notification::fake();

          $displaysWithThisPost = Display::factory(3)->create();
          $displaysNoPost = Display::factory(2)->create();

          foreach (
              $displaysWithThisPost as $display
          ) {
              $this->post->displays()->attach($display->id);

              event(new DisplayPostUpdated($display, $this->post));

              Notification::assertSentTo(
                  $display,
                  PostUpdated::class
              );
          }
          Notification::assertTimesSent(count($displaysWithThisPost), PostUpdated::class);
      }

    /**
     * @test
     */
    public function when_event_is_DisplayPostDeleted_should_notify_raspberry_with_PostDeleted_notification()
    {
        Notification::fake();

        $displaysNoPost = Display::factory(2)->create();
        $displaysWithThisPost = Display::factory(3)->create();
        $displaysWithPostIds = $displaysWithThisPost->pluck('id')->toArray();

        $newDisplaysPost = Display::factory(4)->create();
        $newDisplaysPostIds = $newDisplaysPost->pluck('id')->toArray();

        $this->post->displays()->attach($displaysWithPostIds);

        $this->post->displays()->sync($newDisplaysPostIds);

        foreach ($displaysWithThisPost as $removedDisplay) {
            $raspberry = Raspberry::factory()->create(['display_id' => $removedDisplay->id]);
            event(new DisplayPostDeleted($removedDisplay, $this->post));
            Notification::assertSentTo(
                $raspberry,
                PostDeleted::class
            );
        }
        Notification::assertTimesSent(count($displaysWithThisPost), PostDeleted::class);
    }

      /**
       * @test
       */
      public function when_display_dont_have_raspberry_and_event_is_DisplayPostDeleted_should_notify_raspberry_with_PostDeleted_notification()
      {
          Notification::fake();

          $displaysNoPost = Display::factory(2)->create();
          $displaysWithThisPost = Display::factory(3)->create();
          $displaysWithPostIds = $displaysWithThisPost->pluck('id')->toArray();

          $newDisplaysPost = Display::factory(4)->create();
          $newDisplaysPostIds = $newDisplaysPost->pluck('id')->toArray();

          $this->post->displays()->attach($displaysWithPostIds);

          $this->post->displays()->sync($newDisplaysPostIds);

          foreach ($displaysWithThisPost as $displayWithThisPost) {
              event(new DisplayPostDeleted($displayWithThisPost, $this->post));
              Notification::assertSentTo(
                  $displayWithThisPost,
                  PostDeleted::class
              );
          }
          Notification::assertTimesSent(count($displaysWithThisPost), PostDeleted::class);
      }
}
