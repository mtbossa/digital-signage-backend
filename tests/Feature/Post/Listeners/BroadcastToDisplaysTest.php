<?php


namespace Post\Listeners;

use App\Events\Post\ShouldEndPost;
use App\Events\Post\ShouldStartPost;
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
    public function when_event_is_ShouldStartPost_should_notify_all_displays_that_have_this_post_with_PostStarted_notification(
    )
    {
        Notification::fake();
        Bus::fake();
        Event::fakeExcept([ShouldStartPost::class]);


        $displaysWithThisPost = Display::factory(3)->create();
        $displaysNoPost = Display::factory(2)->create();

        foreach (
            $displaysWithThisPost as $display
        ) {
            $this->post->displays()->attach($display->id);
        }

        event(new ShouldStartPost($this->post));

        Notification::assertSentTo(
            $displaysWithThisPost,
            PostStarted::class
        );
    }

    /**
     * @test
     */
    public function when_event_is_ShouldEndPost_should_notify_all_displays_that_have_this_post_with_PostEnded_notification(
    )
    {
        Notification::fake();
        Bus::fake();
        Event::fakeExcept([ShouldEndPost::class]);


        $displaysWithThisPost = Display::factory(3)->create();
        $displaysNoPost = Display::factory(2)->create();

        foreach (
            $displaysWithThisPost as $display
        ) {
            $this->post->displays()->attach($display->id);
        }

        event(new ShouldEndPost($this->post));

        Notification::assertSentTo(
            $displaysWithThisPost,
            PostEnded::class
        );
    }

    /**
     * @test
     */
    public function when_event_is_ShouldStartPost_should_notify_all_displays_that_have_this_post_one_time_each_with_PostStarted_notification(
    )
    {
        Notification::fake();
        Bus::fake();
        Event::fakeExcept([ShouldStartPost::class]);

        $displaysWithThisPost = Display::factory(3)->create();
        $displaysNoPost = Display::factory(2)->create();

        foreach (
            $displaysWithThisPost as $display
        ) {
            $this->post->displays()->attach($display->id);
        }

        event(new ShouldStartPost($this->post));

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
    public function when_event_is_ShouldEndPost_should_notify_all_displays_that_have_this_post_one_time_each_with_PostEnded_notification(
    )
    {
        Notification::fake();
        Bus::fake();
        Event::fakeExcept([ShouldEndPost::class]);

        $displaysWithThisPost = Display::factory(3)->create();
        $displaysNoPost = Display::factory(2)->create();

        foreach (
            $displaysWithThisPost as $display
        ) {
            $this->post->displays()->attach($display->id);
        }

        event(new ShouldEndPost($this->post));

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
    public function when_event_is_ShouldStartPost_should_not_notify_displays_that_dont_have_this_post_with_PostStarted_notification(
    )
    {
        Notification::fake();
        Bus::fake();
        Event::fakeExcept([ShouldStartPost::class]);

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

        event(new ShouldStartPost($this->post));

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
    public function when_event_is_ShouldEndPost_should_not_notify_displays_that_dont_have_this_post_with_PostEnded_notification(
    )
    {
        Notification::fake();
        Bus::fake();
        Event::fakeExcept([ShouldEndPost::class]);

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

        event(new ShouldEndPost($this->post));

        foreach ($notNotifiableDisplays as $display) {
            Notification::assertNotSentTo(
                $display,
                PostEnded::class
            );
        }
    }
}
