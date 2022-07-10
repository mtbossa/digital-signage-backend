<?php


namespace Post\Listeners;

use App\Events\ShouldEndPost;
use App\Events\ShouldStartPost;
use App\Models\Display;
use App\Models\Post;
use App\Models\Raspberry;
use App\Notifications\PostEnded;
use App\Notifications\PostStarted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class BroadcastToRaspberriesTest extends TestCase
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
    public function when_event_is_ShouldStartPost_should_notify_only_raspberries_which_are_not_null(
    )
    {
        Notification::fake();
        Bus::fake();
        Event::fakeExcept([ShouldStartPost::class]);

        $displaysWithRaspberry = Display::factory(3)->create();
        $displayWithoutRaspberry = Display::factory(2)->create();

        foreach (
            [...$displaysWithRaspberry, ...$displayWithoutRaspberry] as $display
        ) {
            $this->post->displays()->attach($display->id);
        }

        foreach ($displayWithoutRaspberry as $display) {
            Raspberry::factory()->create();
        }

        foreach ($displaysWithRaspberry as $display) {
            $raspberry = Raspberry::factory()->make()->toArray();
            $display->raspberry()->create($raspberry);
        }

        event(new ShouldStartPost($this->post));

        Notification::assertTimesSent(count($displaysWithRaspberry),
            PostStarted::class);
        Notification::assertSentTo(
            Raspberry::query()->whereNotNull('display_id')->get(),
            PostStarted::class
        );
        Notification::assertNotSentTo(
            Raspberry::query()->whereNull('display_id')->get(),
            PostStarted::class
        );
    }

    /**
     * @test
     */
    public function when_event_is_ShouldStartPost_should_notify_raspberries_with_PostStarted(
    )
    {
        Notification::fake();
        Bus::fake();
        Event::fakeExcept([ShouldStartPost::class]);

        $displaysWithRaspberry = Display::factory(3)->create();
        $displayWithoutRaspberry = Display::factory(2)->create();

        foreach (
            [...$displaysWithRaspberry, ...$displayWithoutRaspberry] as $display
        ) {
            $this->post->displays()->attach($display->id);
        }

        foreach ($displayWithoutRaspberry as $display) {
            Raspberry::factory()->create();
        }

        foreach ($displaysWithRaspberry as $display) {
            $raspberry = Raspberry::factory()->make()->toArray();
            $display->raspberry()->create($raspberry);
        }

        event(new ShouldStartPost($this->post));

        Notification::assertTimesSent(count($displaysWithRaspberry),
            PostStarted::class);
        Notification::assertNotSentTo([
            ...$displaysWithRaspberry, ...$displayWithoutRaspberry
        ], PostEnded::class);

    }

    /**
     * @test
     */
    public function when_event_is_ShouldEndPost_should_notify_only_raspberries_which_are_not_null(
    )
    {
        Notification::fake();
        Bus::fake();
        Event::fakeExcept([ShouldEndPost::class]);

        $displaysWithRaspberry = Display::factory(3)->create();
        $displayWithoutRaspberry = Display::factory(2)->create();

        foreach (
            [...$displaysWithRaspberry, ...$displayWithoutRaspberry] as $display
        ) {
            $this->post->displays()->attach($display->id);
        }

        foreach ($displayWithoutRaspberry as $display) {
            Raspberry::factory()->create();
        }

        foreach ($displaysWithRaspberry as $display) {
            $raspberry = Raspberry::factory()->make()->toArray();
            $display->raspberry()->create($raspberry);
        }

        event(new ShouldEndPost($this->post));

        Notification::assertTimesSent(count($displaysWithRaspberry),
            PostEnded::class);
        Notification::assertSentTo(
            Raspberry::query()->whereNotNull('display_id')->get(),
            PostEnded::class
        );
        Notification::assertNotSentTo(
            Raspberry::query()->whereNull('display_id')->get(),
            PostEnded::class
        );
    }

    /**
     * @test
     */
    public function when_event_is_ShouldEndPost_should_notify_raspberries_with_PostEnded(
    )
    {
        Notification::fake();
        Bus::fake();
        Event::fakeExcept([ShouldEndPost::class]);

        $displaysWithRaspberry = Display::factory(3)->create();
        $displayWithoutRaspberry = Display::factory(2)->create();

        foreach (
            [...$displaysWithRaspberry, ...$displayWithoutRaspberry] as $display
        ) {
            $this->post->displays()->attach($display->id);
        }

        foreach ($displayWithoutRaspberry as $display) {
            Raspberry::factory()->create();
        }

        foreach ($displaysWithRaspberry as $display) {
            $raspberry = Raspberry::factory()->make()->toArray();
            $display->raspberry()->create($raspberry);
        }

        event(new ShouldEndPost($this->post));

        Notification::assertTimesSent(count($displaysWithRaspberry),
            PostEnded::class);
        Notification::assertNotSentTo([
            ...$displaysWithRaspberry, ...$displayWithoutRaspberry
        ], PostStarted::class);

    }
}
